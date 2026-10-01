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
import intlTelInput from 'intl-tel-input';

select2(window, $);

const SEARCH_THRESHOLD = 8;

/**
 * A field is skipped when the user cannot see it (e.g. hidden by x-show because another option
 * was chosen). Select2 and rich-text fields are judged by their visible widget. In multi-step
 * forms, fields on other steps are still validated on submit.
 */
function isIgnored(element) {
    if (element.type === 'hidden' && !element.hasAttribute('data-validate-hidden')) return true;

    const step = element.form?.hasAttribute('data-steps') ? element.closest('[data-step]') : null;
    const boundary = step ?? element.form;
    let node = element.type === 'hidden' || element.classList.contains('select2-hidden-accessible') ? element.parentElement : element;

    for (; node && node !== boundary; node = node.parentElement) {
        if (getComputedStyle(node).display === 'none') return true;
    }

    return false;
}

$.validator.setDefaults({
    ignore: (index, element) => isIgnored(element),
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
        } else if ($el.closest('.iti').length) {
            error.insertAfter($el.closest('.iti'));
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
        .not('[type=submit], [type=button]')
        .filter((index, element) => !isIgnored(element))
        .each(function () {
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

/** File no larger than data-rule-maxbytes (the server's real upload limit). */
$.validator.addMethod('maxbytes', function (value, element, max) {
    return this.optional(element) || [...(element.files || [])].every((file) => file.size <= Number(max));
});

/** Valid for the chosen country (checked by intl-tel-input's bundled libphonenumber data). */
$.validator.addMethod('intlphone', function (value, element) {
    if (this.optional(element) || !element.iti) return true;
    return element.iti.isValidNumber() !== false;
}, 'Enter a valid phone number for the selected country.');

/**
 * Phone fields (x-form.phone): country picker with search, typed number formatted as you go.
 * The hidden companion input always holds the full international number (E.164).
 */
function initPhones(root) {
    $(root).find('input[data-phone]').each(function () {
        if (this.iti) return;
        const input = this;
        const hidden = input.parentElement.querySelector('input[data-phone-value]');

        input.iti = intlTelInput(input, {
            initialCountry: input.dataset.phoneCountry || 'in',
            countryOrder: [input.dataset.phoneCountry || 'in'],
            separateDialCode: true,
            strictMode: true,
            formatAsYouType: true,
            countrySearch: true,
            dropdownContainer: input.closest('[role=dialog]') ? null : document.body,
            loadUtils: () => import('intl-tel-input/utils'),
        });

        const sync = () => {
            const typed = input.value.trim();
            hidden.value = typed ? (input.iti.getNumber() || typed) : '';
        };
        const revalidate = () => {
            const validator = input.form && $(input.form).data('validator');
            if (validator && (input.name in validator.submitted || input.name in validator.invalid)) validator.element(input);
        };

        input.addEventListener('input', sync);
        input.addEventListener('countrychange', () => { sync(); revalidate(); });
        input.iti.promise.then(sync);
    });
}

/* ------------------------------------------------------------------
 * Manuscript rules — mirror App\Http\Requests\ManuscriptSubmissionRequest
 * so authors see problems before submitting (the server still checks).
 * ------------------------------------------------------------------ */
const wordCount = (text) => (text.trim().match(/\S+/g) || []).length;
const keywordList = (text) => [...new Set(text.split(',').map((k) => k.trim().toLowerCase()).filter(Boolean))];

/** data-rule-keywords="3,6": between min and max distinct comma-separated keywords. */
$.validator.addMethod('keywords', function (value, element, param) {
    const [min, max] = String(param).split(',').map(Number);
    const count = keywordList(value).length;
    return this.optional(element) || (count >= min && count <= max);
}, (param) => {
    const [min, max] = String(param).split(',');
    return `Enter between ${min} and ${max} different keywords, separated by commas.`;
});

/** data-rule-maxwords="250" */
$.validator.addMethod('maxwords', function (value, element, max) {
    return this.optional(element) || wordCount(value) <= Number(max);
}, (max, element) => `Keep this within ${max} words (currently ${wordCount(element.value)}).`);

/** data-rule-docx: a Word .docx file. */
$.validator.addMethod('docx', function (value, element) {
    return this.optional(element) || [...(element.files || [])].every((file) => file.name.toLowerCase().endsWith('.docx'));
}, 'Upload a Word document (.docx). Other formats such as .doc or .pdf are not accepted.');

/**
 * data-rule-wordrange="#content_category": the counted words (set on the file input after it is
 * read in the browser) must fit the min/max of the selected content category option.
 */
$.validator.addMethod('wordrange', function (value, element, selector) {
    if (this.optional(element)) return true;
    if (element.dataset.unreadable === '1') return false;
    const words = Number(element.dataset.words);
    const option = document.querySelector(selector)?.selectedOptions?.[0];
    if (!element.dataset.words || !option?.dataset.min) return true; // not counted yet / no category: the server checks
    return words >= Number(option.dataset.min) && words <= Number(option.dataset.max);
}, (selector, element) => {
    if (element.dataset.unreadable === '1') return 'This file could not be read. Make sure it is a valid Word (.docx) document that is not password-protected.';
    const option = document.querySelector(selector)?.selectedOptions?.[0];
    return `Your manuscript has ${Number(element.dataset.words).toLocaleString()} words; ${option?.dataset.name} accepts ${Number(option?.dataset.min).toLocaleString()}–${Number(option?.dataset.max).toLocaleString()} words.`;
});

/** data-rule-offered: the selected option is not marked data-offered="0". */
$.validator.addMethod('offered', function (value, element) {
    return this.optional(element) || element.selectedOptions?.[0]?.dataset.offered !== '0';
}, 'This content category is not open to your author category. Please choose another one.');

/** Live "12 / 250 words" and "4 keywords" counters under fields with those rules. */
function initCounters(root) {
    $(root).find('[data-rule-maxwords], [data-rule-keywords]').each(function () {
        if (this.dataset.counter) return;
        this.dataset.counter = '1';
        const field = this;
        const counter = document.createElement('p');
        counter.className = 'text-xs text-muted-foreground tabular-nums';
        counter.setAttribute('aria-live', 'polite');
        field.closest('[data-field]')?.appendChild(counter);

        const update = () => {
            if (field.dataset.ruleMaxwords) {
                const n = wordCount(field.value);
                const max = Number(field.dataset.ruleMaxwords);
                counter.textContent = `${n} / ${max} words`;
                counter.classList.toggle('text-destructive', n > max);
            } else {
                const [min, max] = field.dataset.ruleKeywords.split(',').map(Number);
                const n = keywordList(field.value).length;
                counter.textContent = n === 1 ? '1 keyword' : `${n} keywords`;
                counter.classList.toggle('text-destructive', n > max || (n > 0 && n < min && document.activeElement !== field));
            }
        };
        field.addEventListener('input', update);
        field.addEventListener('blur', update);
        update();
    });
}

export function initForms(root = document) {
    initSelect2(root);
    initPhones(root);
    initCounters(root);
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

// Check a chosen file's size straight away, before the form is submitted.
document.addEventListener('change', (event) => {
    const input = event.target;
    if (input.matches?.('input[type=file][data-rule-maxbytes]') && $(input.form).data('validator')) $(input).valid();
});
