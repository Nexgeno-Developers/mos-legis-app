toastr.options = {
    closeButton: true,                  // Enables a close button for user control.
    progressBar: true,                  // Displays a progress bar for timing.
    newestOnTop: true,                  // Ensures the newest toast appears on top.
    positionClass: "toast-bottom-left", // Positions the toast on the top-right corner.
    preventDuplicates: false,           // Prevents duplicate toasts from showing.
    onclick: null,                      // No action is triggered by clicking the toast.
    showDuration: "300",                // Toast appears smoothly within 300ms.
    hideDuration: "300",                // Toast hides smoothly within 300ms.
    timeOut: "6000",                    // Auto-hides the toast after 5 seconds.
    extendedTimeOut: "500",             // Waits 1 second before fully disappearing after hover.
    showEasing: "swing",                // Adds a smooth easing effect on showing.
    hideEasing: "swing",                // Adds a smooth easing effect on hiding.
    showMethod: "fadeIn",               // Toast fades in when displayed.
    hideMethod: "fadeOut",              // Toast fades out when dismissed.
};


//bootstarp modals
function largeModal(url, header) {
    $("#largeModal .modal-title").html("");
    $("#largeModal .modal-body").html(`
        <div class="text-center">
            <div class="spinner-border text-light m-2" role="status"></div>
        </div>
    `);

    $("#largeModal").modal("show");
    $.ajax({
        url: url,
        success: function (response) {
            $("#largeModal .modal-body").html(response);
            $("#largeModal .modal-title").html(header);
        },
    });
}

function smallModal(url, header) {
    $("#smallModal .modal-title").html("");
    $("#smallModal .modal-body").html(`
        <div class="text-center">
            <div class="spinner-border text-light m-2" role="status"></div>
        </div>
    `);

    $("#smallModal").modal("show");
    $.ajax({
        url: url,
        success: function (response) {
            $("#smallModal .modal-body").html(response);
            $("#smallModal .modal-title").html(header);
        },
    });
}

function confirmModal(delete_url, param) {
    $("#confirmModal").modal("show");
    callBackFunction = param;
    document.getElementById("delete_form").setAttribute("action", delete_url);
}


function cloneConfirm(url) {
    $('#cloneModal').modal('show');
    document.getElementById('clone_confirm_btn').setAttribute('href', url);
}

$(".ajaxDeleteForm").submit(function (e) {
    e.preventDefault();
    alert(1);
    var form = $(this);
    ajaxSubmit(e, form, callBackFunction);
});

// Handle bulk action forms
$(document).on('submit', '.ajaxBulkForm', function (e) {
    e.preventDefault();
    var form = $(this);
    ajaxSubmitBulk(e, form, callBackFunction);
});

function closeModel() {}

function closeConfirmModel() {
    $("#confirmModal").modal("hide");
}

//jquery validator
// function initValidate(selector)
// {
//     $(selector).validate({});
// }

// function initValidate(selector) {
//     $(selector).validate({
//         ignore: "", // Include hidden fields in validation
//         errorPlacement: function (error, element) {
//             if (element.prop("type") === "hidden") {
//                 // Add error message for hidden fields in a specific container or after the field
//                 error.insertAfter(element);
//             } else {
//                 error.insertAfter(element); // Default placement for visible fields
//             }
//         },
//         rules: {
//             // Example: Define rules for fields here
//             hiddenFieldName: {
//                 required: true
//             }
//         },
//         messages: {
//             // Example: Define custom messages for hidden fields
//             hiddenFieldName: {
//                 required: "This hidden field is required."
//             }
//         }
//     });
// }

// function initValidate(selector)
// {
//     $(selector).validate({
//         ignore: "", // Include hidden fields in validation
//         //errorElement: 'div',
//         errorPlacement: function (error, element) {
//         error.addClass('invalid-feedback');
//             element.closest('.form-group').append(error);
//         },            
//         highlight: function (element, errorClass, validClass) {
//             //$(element).addClass('is-invalid');
//         },
//         unhighlight: function (element, errorClass, validClass) {
//             //$(element).removeClass('is-invalid');
//         }       
//     });
// }

function initValidate(selector) {
    const $form = $(selector);

    $form.validate({
        ignore: "", // Include hidden fields in validation
        errorPlacement: function (error, element) {
            error.addClass('invalid-feedback');
            element.closest('.form-group').append(error);
        },
        highlight: function (element, errorClass, validClass) {
            //$(element).addClass('is-invalid');
        },
        unhighlight: function (element, errorClass, validClass) {
            //$(element).removeClass('is-invalid');
        },
    });

    // Dynamically remove error when value changes
    $form.on('change input', 'input, select, textarea', function () {
        const $field = $(this);
        if ($field.valid()) {
            $field.removeClass('is-invalid');
            $field.closest('.form-group').find('.invalid-feedback').remove();
        }
    });
}


//select2
// function initSelect2(selector = '.select2') {
//     $(selector).select2();
// }

function initSelect2(selector = '.select2') {
    $(selector).each(function () {
        let modal = $(this).closest('.modal'); // finds largeModal or smallModal automatically

        const $select = $(this);
        const hasOptionDescriptions = $select.find('option[data-description]').length > 0;

        const select2Options = {
            width: '100%',
            dropdownParent: modal.length ? modal : $(document.body)
        };

        if (hasOptionDescriptions) {
            // Make Select2 search match both option text and option[data-description].
            // templateResult only affects rendering; matcher affects filtering/search results.
            select2Options.matcher = function (params, data) {
                const term = (params.term || '').toString().toLowerCase();

                // Keep placeholder/empty entries visible when no term is provided.
                if (!term) return data;

                // data.text is the visible label for <option>.
                const text = (data.text || '').toString().toLowerCase();

                const desc =
                    data.element && $(data.element).attr('data-description')
                        ? $(data.element).attr('data-description').toString().toLowerCase()
                        : '';

                // Basic substring match (case-insensitive) for both fields.
                if (text.includes(term) || desc.includes(term)) return data;

                return null;
            };

            // Render option description under the label for layout dropdowns.
            select2Options.templateResult = function (state) {
                if (!state.id) return state.text; // e.g. placeholder option

                const desc = $(state.element).attr('data-description');
                if (!desc) return state.text;

                const $wrap = $('<span/>');
                $wrap.append($('<span/>').text(state.text));
                $wrap.append('<br>');
                $wrap.append(
                    $('<em/>')
                        .text(desc)
                        .css({ 'font-size': '0.85em', 'font-style': 'italic', 'opacity': '0.85' })
                );

                return $wrap;
            };

            // Keep the selected value clean (label only).
            select2Options.templateSelection = function (state) {
                if (!state.id) return state.text;
                return state.text;
            };
        }

        $select.select2(select2Options);
    });
}


//Form Submition
function ajaxSubmit(e, form, callBackFunction) {
    // Check if form has validation, if not, skip validation check
    var isValid = true;
    if (form.length && typeof form.valid === 'function') {
        try {
            isValid = form.valid();
        } catch(err) {
            // If validation is not initialized, consider form valid
            isValid = true;
        }
    }
    
    if(isValid) {
        e.preventDefault();
        
        var btn = $(form).find('button[type="submit"]');
        var btn_text = $(btn).html();
        $(btn).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
        $(btn).css('opacity', '0.7');
        $(btn).css('pointer-events', 'none');

        var action = form.attr('action');
        var formElement = e.target;
        var data = new FormData(formElement);
        $.ajax({
            type: "POST",
            url: action,
            processData: false,
            contentType: false,
            dataType: 'json',
            data: data,
            success: function(response)
            {
                $(btn).html(btn_text);
                $(btn).css('opacity', '1');
                $(btn).css('pointer-events', 'inherit');
            
                if (response.status) {
                    // If response status is true, show a success notification
                    Command: toastr["success"](response.notification, "Success");
                    if (callBackFunction && typeof callBackFunction === 'function') {
                        callBackFunction(response); // Callback function if success
                    }
                } else {
                    // Handle case when response status is false (error from server)
                    var errors = '';
                    
                    // Check if response.notification is an object (validation errors)
                    if (typeof response.notification === 'object') {
                        $.each(response.notification, function(key, msg) {
                            // If msg is an array (multiple errors for the same field)
                            if (Array.isArray(msg)) {
                                $.each(msg, function(index, message) {
                                    errors += '<div>' + message + '</div>';
                                });
                            } else {
                                errors += '<div>' + msg + '</div>';
                            }
                        });
                    } else {
                        // Fallback error message if notification is not an object
                        errors = response.notification || 'An unexpected error occurred.';
                    }
                    
                    // Show the errors in a toastr notification
                    Command: toastr["error"](errors, "Alert");
                }
            },
            error: function(xhr) {
                $(btn).html(btn_text);
                $(btn).css('opacity', '1');
                $(btn).css('pointer-events', 'inherit');
            
                // Check if the status code is 422 (Laravel validation errors)
                if (xhr.status === 422) {
                    var errors = '';
                    var response = xhr.responseJSON; // Get the response from Laravel
            
                    // Iterate over Laravel validation errors
                    if (response && response.errors) {
                        $.each(response.errors, function(key, msg) {
                            // If msg is an array (multiple errors for the same field)
                            if (Array.isArray(msg)) {
                                $.each(msg, function(index, message) {
                                    errors += '<div>' + message + '</div>';
                                });
                            } else {
                                errors += '<div>' + msg + '</div>';
                            }
                        });
                    }
                    // Show the validation errors using toastr
                    Command: toastr["error"](errors, "Alert");
                } else {
                    // Handle unexpected errors (non-validation errors)
                    Command: toastr["error"]("An unexpected error occurred. Please try again later.", "Error");
                }
            }
        });
    }else {
        toastr.error('Please make sure to fill all the necessary fields');
    }
}

// Bulk form submission (no validation required)
function ajaxSubmitBulk(e, form, callBackFunction) {
    e.preventDefault();
    
    var btn = $(form).find('button[type="submit"]');
    var btn_text = $(btn).html();
    $(btn).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
    $(btn).css('opacity', '0.7');
    $(btn).css('pointer-events', 'none');

    var action = form.attr('action');
    var formElement = e.target;
    var data = new FormData(formElement);
    
    $.ajax({
        type: "POST",
        url: action,
        processData: false,
        contentType: false,
        dataType: 'json',
        data: data,
        success: function(response)
        {
            $(btn).html(btn_text);
            $(btn).css('opacity', '1');
            $(btn).css('pointer-events', 'inherit');
        
            if (response.status) {
                // If response status is true, show a success notification
                Command: toastr["success"](response.notification, "Success");
                if (callBackFunction && typeof callBackFunction === 'function') {
                    callBackFunction(response); // Callback function if success
                }
            } else {
                // Handle case when response status is false (error from server)
                var errors = '';
                
                // Check if response.notification is an object (validation errors)
                if (typeof response.notification === 'object') {
                    $.each(response.notification, function(key, msg) {
                        // If msg is an array (multiple errors for the same field)
                        if (Array.isArray(msg)) {
                            $.each(msg, function(index, message) {
                                errors += '<div>' + message + '</div>';
                            });
                        } else {
                            errors += '<div>' + msg + '</div>';
                        }
                    });
                } else {
                    // Fallback error message if notification is not an object
                    errors = response.notification || 'An unexpected error occurred.';
                }
                
                // Show the errors in a toastr notification
                Command: toastr["error"](errors, "Alert");
                // Close modals on error
                $('#bulkDeleteModal').modal('hide');
                $('#bulkActiveModal').modal('hide');
                $('#bulkInactiveModal').modal('hide');
            }
        },
        error: function(xhr) {
            $(btn).html(btn_text);
            $(btn).css('opacity', '1');
            $(btn).css('pointer-events', 'inherit');
        
            // Check if the status code is 422 (Laravel validation errors)
            if (xhr.status === 422) {
                var errors = '';
                var response = xhr.responseJSON; // Get the response from Laravel
        
                // Iterate over Laravel validation errors
                if (response && response.errors) {
                    $.each(response.errors, function(key, msg) {
                        // If msg is an array (multiple errors for the same field)
                        if (Array.isArray(msg)) {
                            $.each(msg, function(index, message) {
                                errors += '<div>' + message + '</div>';
                            });
                        } else {
                            errors += '<div>' + msg + '</div>';
                        }
                    });
                }
                // Show the validation errors using toastr
                Command: toastr["error"](errors, "Alert");
            } else {
                // Handle unexpected errors (non-validation errors)
                Command: toastr["error"]("An unexpected error occurred. Please try again later.", "Error");
            }
            // Close modals on error
            $('#bulkDeleteModal').modal('hide');
            $('#bulkActiveModal').modal('hide');
            $('#bulkInactiveModal').modal('hide');
        }
    });
}

function initDatatable(selector){
    $(selector).DataTable({
        paging: true,
        searching: true,
        ordering: true,
        info: true
    });   
}

/**
 * Allow TinyMCE popups to receive focus inside Bootstrap 5 modals
 * Safe for pages, modals, dropdowns, and dynamic content
 */
document.addEventListener('focusin', function (e) {
    if (
        e.target.closest('.tox-dialog') ||
        e.target.closest('.tox-menu') ||
        e.target.closest('.tox-tinymce-aux')
    ) {
        e.stopImmediatePropagation();
    }
});

function initTextEditor() {
    tinymce.init({
        license_key: "gpl", // ✅ Tell TinyMCE you are using GPL/self-hosted
        selector: '.text-editor',
        relative_urls: false,             // ✅ Prevents relative paths like ../../
        remove_script_host: false,        // ✅ Keeps full domain
        convert_urls: true,               // ✅ Forces conversion of URLs
        document_base_url: $('meta[name="front-file-base-url"]').attr('content'),        
        statusbar: false,
        height: 300, // Set the desired height
        //valid_elements: '*[*]', // Allows all HTML elements and attributes
        //extended_valid_elements: 'p[style|class],a[href|target],strong,br',
        //cleanup: false, // Prevent TinyMCE from cleaning up your HTML
        //forced_root_block: false, // Avoid wrapping content in <p> if not needed
        //entity_encoding: 'raw', // Preserve HTML entities as-is        
        plugins: 'anchor advlist autolink lists link image charmap preview hr pagebreak ' +
                'searchreplace wordcount visualblocks code fullscreen insertdatetime media nonbreaking ' +
                'save table directionality emoticons template paste help',
        // toolbar: 'undo redo | formatselect | bold italic underline strikethrough | ' +
        //         'alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | ' +
        //         'link anchor image media | forecolor backcolor removeformat | ' +
        //         'preview code fullscreen | insertdatetime table emoticons | help',
        setup: function(editor) {
            editor.on('change keyup', function() {
                editor.save(); // Sync content back to the <textarea>
                $(editor.getElement()).valid(); // Trigger validation on the <textarea>
                console.log(editor.getElement());
            });
        }
    });    
}

function initAizPlugins(){
    if (typeof AIZ !== 'undefined' && AIZ.uploader) {
        // init aiz plugins, extra options
        AIZ.extra.initActiveMenu();
        AIZ.extra.mobileNavToggle();
        AIZ.extra.deleteConfirm();
        AIZ.extra.multiModal();
        AIZ.extra.inputRating();
        AIZ.extra.bsCustomFile();
        AIZ.extra.stopPropagation();
        AIZ.extra.outsideClickHide();
        AIZ.extra.scrollToBottom();
        AIZ.extra.classToggle();
        AIZ.extra.collapseSidebar();
        AIZ.extra.autoScroll();
        AIZ.extra.addMore();
        AIZ.extra.removeParent();
        AIZ.extra.selectHideShow();
        AIZ.extra.plusMinus();
        AIZ.extra.hovCategoryMenu();
        AIZ.extra.clickCategoryMenu();
        AIZ.extra.hovUserTopMenu();
        AIZ.extra.trimAppUrl();
        AIZ.extra.acceptCookie();
        AIZ.extra.setSession();
        AIZ.extra.showSessionPopup();

        AIZ.plugins.metismenu();
        AIZ.plugins.bootstrapSelect();
        AIZ.plugins.tagify();
        AIZ.plugins.textEditor();
        AIZ.plugins.tooltip();
        AIZ.plugins.countDown();
        AIZ.plugins.countDownBox();
        AIZ.plugins.countDownCircle();
        AIZ.plugins.dateRange();
        AIZ.plugins.timePicker();
        AIZ.plugins.colorPicker();
        AIZ.plugins.fooTable();
        AIZ.plugins.slickCarousel();
        AIZ.plugins.noUiSlider();
        AIZ.plugins.zoom();
        AIZ.plugins.jsSocials();

        AIZ.uploader.initForInput();
        AIZ.uploader.removeAttachment();
        AIZ.uploader.previewGenerate();
    }    
}

function initOtherPlugins(){
    initTextEditor();
    initSelect2();
}

$(document).ready(function(){
    initTextEditor();
    initSelect2();
});