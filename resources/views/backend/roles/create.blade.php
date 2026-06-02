<form class="form" id="create" action="{{ route($module . '.store') }}" method="POST">
    @csrf
    <div class="row">
        <!-- Role -->
        <div class="col-sm-12">
            <div class="form-group mb-2">
                <label for="name" class="form-label">{{__("labels.name")}} <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" required>
            </div>
        </div>
    </div>

    <!-- Submit Button -->
    <div class="col-sm-12">
        <div class="text-center mt-1">
            <button type="submit" class="btn btn-primary">{{__("labels.create")}}</button>
        </div>
    </div>
</form>

<!-- Initialization JS -->
<!-- <script src="{{ asset('assets/backend/js/init.js') }}"></script> -->

<script>
$(document).ready(function() {
    initValidate('#create');
    initTextEditor();
    initSelect2('.select2');

    $("#create").submit(function(e) {
        var form = $(this);
        ajaxSubmit(e, form, callbackCreateForm);
    });

    const callbackCreateForm = function(response) {
        setTimeout(function() {
            location.reload();
        }, 1500);
    }
});
</script>
