/**
 * Application-wide form behaviour:
 *  - jQuery Validate on every <form> (replaces native browser validation). Rules come from
 *    the markup: required, type=email|url|number, min/max, minlength/maxlength, plus
 *    data-rule-* / data-msg-* attributes (e.g. password confirmation uses data-rule-equalto).
 *  - Select2 on every <select class="field-input"> (opt out with data-native). The search
 *    box appears automatically when a list is long; multi-selects always get it.
 *
 * Select2 keeps the original <select> in sync and re-dispatches a native "change" event
 * so Alpine x-model / onchange handlers keep working.
 */
import $ from 'jquery';
import 'jquery-validation';
import select2 from 'select2';

select2(window, $);

const SEARCH_THRESHOLD = 8;

$.validator.setDefaults({
    // Hidden fields are skipped, except Select2 selects and rich-text (Trix) inputs.
    ignore: ':hidden:not(.select2-hidden-accessible):not([data-validate-hidden])',
    errorElement: 'p',
    errorClass: 'field-error text-sm text-destructive',
    validClass: '',
    // Typing is handled by the "input" listener below (covers paste, autofill, file/date pickers too).
    onkeyup: false,
    focusInvalid: true,
    highlight(element) {
        $(element).attr('aria-invalid', 'true');
        $(element).next('.select2').addClass('select2-invalid');
        $(element).siblings('trix-editor').addClass('trix-invalid');
    },
    unhighlight(element) {
        $(element).removeAttr('aria-invalid');
        $(element).next('.select2').removeClass('select2-invalid');
        $(element).siblings('trix-editor').removeClass('trix-invalid');
    },
    errorPlacement(error, element) {
        const $el = $(element);
        error.attr('role', 'alert');

        if ($el.hasClass('select2-hidden-accessible')) {
            error.insertAfter($el.next('.select2'));
        } else if (element.type === 'checkbox' || element.type === 'radio') {
            error.insertAfter($el.closest('label'));
        } else if ($el.is('[data-validate-hidden]')) {
            error.insertAfter($el.siblings('trix-editor').first());
        } else if ($el.parent().is('[data-input-wrap]')) {
            error.insertAfter($el.parent());
        } else {
            error.insertAfter($el);
        }
    },
});

$.extend($.validator.messages, {
    required: 'This field is required.',
    email: 'Enter a valid email address.',
    url: 'Enter a valid URL, including https://.',
    equalTo: 'This does not match.',
});

/** Validate only the visible fields inside a container (used by the multi-step form). */
export function validateWithin(container) {
    const $form = $(container).closest('form');
    const validator = $form.data('validator') || $form.validate();
    let valid = true;

    $(container)
        .find('input, select, textarea')
        .filter(':visible, .select2-hidden-accessible, [data-validate-hidden]')
        .not('[type=hidden]:not([data-validate-hidden]), [type=submit], [type=button]')
        .each(function () {
            if ($(this).closest('[data-step]').is(':hidden')) return;
            if (!validator.element(this)) valid = false;
        });

    if (!valid) validator.focusInvalid();

    return valid;
}

function initValidation(root) {
    $(root).find('form').addBack('form').each(function () {
        const $form = $(this);
        if ($form.data('validator') || $form.is('[data-no-validate]')) return;

        const options = {};

        // Multi-step forms validate every step on submit and jump to the first invalid one.
        if ($form.is('[data-steps]')) {
            options.ignore = '[type=hidden]:not([data-validate-hidden])';
            options.invalidHandler = (event, validator) => {
                const first = validator.errorList[0]?.element;
                const step = first ? $(first).closest('[data-step]').data('step') : null;
                if (step !== null && step !== undefined) {
                    this.dispatchEvent(new CustomEvent('go-to-step', { detail: Number(step) }));
                }
            };
        }

        $form.validate(options);
    });

    // A server-side error disappears once the user edits that field.
    $(root).find('[data-server-error]').each(function () {
        const $error = $(this);
        $error.closest('[data-field]').find('input, select, textarea').one('input change', () => $error.remove());
    });
}

function initSelect2(root) {
    $(root).find('select.field-input:not([data-native])').each(function () {
        const $select = $(this);
        if ($select.hasClass('select2-hidden-accessible')) return;

        const multiple = $select.prop('multiple');
        const optionCount = $select.find('option').filter((_, o) => o.value !== '').length;
        const placeholderOption = $select.find('option[value=""]').first();
        const dialog = $select.closest('[role=dialog]');

        $select.select2({
            width: '100%',
            placeholder: $select.data('placeholder') || placeholderOption.text() || 'Select…',
            allowClear: !multiple && !$select.prop('required') && placeholderOption.length > 0,
            minimumResultsForSearch: multiple || $select.is('[data-search]') ? 0 : (optionCount > SEARCH_THRESHOLD ? 0 : Infinity),
            dropdownParent: dialog.length ? dialog : $(document.body),
            closeOnSelect: !multiple,
        });

        // Keep Alpine (x-model) and inline onchange handlers informed, and re-validate.
        $select.on('select2:select select2:unselect select2:clear', () => {
            this.dispatchEvent(new Event('change', { bubbles: true }));
            if ($select.closest('form').data('validator')) $select.valid();
        });
    });
}

/** Re-sync Select2 widgets after Alpine changed the underlying <select> values (e.g. edit modals). */
export function refreshSelects(root) {
    $(root).find('select.select2-hidden-accessible').trigger('change.select2');
}

export function initForms(root = document) {
    initSelect2(root);
    initValidation(root);
}

window.$ = window.jQuery = $;
window.refreshSelects = refreshSelects;
window.validateWithin = validateWithin;

// Re-validate a required rich-text (Trix) field as the user types into it.
document.addEventListener('trix-change', (event) => {
    const input = document.getElementById(event.target.getAttribute('input'));
    if (input && $(input.form).data('validator')) $(input).valid();
});

// Live feedback: once a field is showing an error (or the form was submitted), re-check it on
// every change so the message disappears the moment the value becomes valid. Untouched fields
// are left alone while the user is still typing; they are checked when the user leaves them.
$(document).on('input change', 'form input, form select, form textarea', function () {
    const validator = $(this.form).data('validator');
    if (!validator || !this.name) return;
    if (this.name in validator.submitted || this.name in validator.invalid || $(this).attr('aria-invalid') === 'true') {
        validator.element(this);
    }
});
