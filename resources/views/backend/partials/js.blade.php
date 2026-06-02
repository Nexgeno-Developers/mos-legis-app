<!-- Theme Config Js -->
<script src="{{ asset('assets/backend/js/config.js') }}"></script>

<!-- Vendor JS -->
<script src="{{ asset('assets/backend/js/vendor.min.js') }}"></script>

<!-- App JS -->
<script src="{{ asset('assets/backend/js/app.js') }}"></script>

<!-- Additional JS Scripts-->
<script src="{{ asset('assets/backend/js/toastr.min.js') }}"></script>
<script src="{{ asset('assets/backend/js/jquery.validate.min.js') }}"></script>
<script src="https://code.jquery.com/ui/1.14.1/jquery-ui.js"></script>

<!-- Aiz Upload JS -->
<script>
    var AIZ = AIZ || {};
    AIZ.local = {
        nothing_selected: 'Nothing selected',
        nothing_found: 'Nothing found',
        choose_file: 'Choose File',
        file_selected: 'File selected',
        files_selected: 'Files selected',
        add_more_files: 'Add more files',
        adding_more_files: 'Adding more files',
        drop_files_here_paste_or: 'Drop files here, paste or',
        browse: 'Browse',
        upload_complete: 'Upload complete',
        upload_paused: 'Upload paused',
        resume_upload: 'Resume upload',
        pause_upload: 'Pause upload',
        retry_upload: 'Retry upload',
        cancel_upload: 'Cancel upload',
        uploading: 'Uploading',
        processing: 'Processing',
        complete: 'Complete',
        file: 'File',
        files: 'Files',
    }
</script>
<script src="{{ asset('assets/backend/js/web-vendors.js') }}"></script>
<script src="{{ asset('assets/backend/js/web-core.js') }}?{{time()}}" defer></script>

<!-- DataTables Core -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<!-- DataTables Buttons Extension -->
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.flash.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<!-- DataTables Responsive Extension -->
<script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>

<!-- DataTables Select Extension -->
<script src="https://cdn.datatables.net/select/1.7.0/js/dataTables.select.min.js"></script>

<!-- DataTables FixedHeader Extension -->
<script src="https://cdn.datatables.net/fixedheader/3.4.0/js/dataTables.fixedHeader.min.js"></script>

<!-- DataTables FixedColumns Extension -->
<script src="https://cdn.datatables.net/fixedcolumns/4.4.0/js/dataTables.fixedColumns.min.js"></script>

<!-- DataTables KeyTable Extension -->
<script src="https://cdn.datatables.net/keytable/2.8.0/js/dataTables.keyTable.min.js"></script>

<!-- DataTables RowGroup Extension -->
<script src="https://cdn.datatables.net/rowgroup/1.4.0/js/dataTables.rowGroup.min.js"></script>

<!-- tinymce JS -->
<script src="{{ asset('assets/thirdparty-bundle/tinymce/js/tinymce/tinymce.min.js') }}"></script>

<!-- tagify JS -->
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/jQuery.tagify.min.js"></script>

<!-- Backend JS -->
<script src="{{ asset('assets/backend/js/backend.js') }}"></script>

<!-- Recaptcha v3 JS -->
<script src="https://www.google.com/recaptcha/api.js?render={{ config('custom.recaptcha_site_key') }}"></script>
<script>
    function protect_with_recaptcha_v3(formElement, action) {
        event.preventDefault();

        grecaptcha.ready(function () {
            grecaptcha.execute('{{ config('custom.recaptcha_site_key') }}', { action: action }).then(function (token) {
                // Create or update recaptcha_token input
                let tokenInput = formElement.querySelector('[name="recaptcha_token"]');
                if (!tokenInput) {
                    tokenInput = document.createElement('input');
                    tokenInput.type = 'hidden';
                    tokenInput.name = 'recaptcha_token';
                    formElement.appendChild(tokenInput);
                }
                tokenInput.value = token;

                //alert(token);

                // Create or update recaptcha_action input
                let actionInput = formElement.querySelector('[name="recaptcha_action"]');
                if (!actionInput) {
                    actionInput = document.createElement('input');
                    actionInput.type = 'hidden';
                    actionInput.name = 'recaptcha_action';
                    formElement.appendChild(actionInput);
                }
                actionInput.value = action;

                formElement.submit();
            });
        });
    }
</script>
