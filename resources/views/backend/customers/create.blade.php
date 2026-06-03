<form class="form" id="create" action="{{ route($module . '.store') }}" method="POST">
    @csrf
    <div class="row">
        @include('backend.customers.partials.form-fields', ['user' => null, 'details' => $details])
        <div class="col-sm-12">
            <div class="text-center mt-1">
                <button type="submit" class="btn btn-primary">{{ __('labels.create') }}</button>
            </div>
        </div>
    </div>
</form>

<script>
$(document).ready(function() {
    initValidate('#create');
    initSelect2('.select2');
    initAizPlugins();

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
