<form class="form" id="edit" action="{{ route($module . '.update', $user->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="row">
        @include('backend.customers.partials.form-fields', ['user' => $user, 'details' => $details])
        <div class="col-sm-12">
            <div class="text-center mt-1">
                <button type="submit" class="btn btn-primary">{{ __('labels.update') }}</button>
            </div>
        </div>
    </div>
</form>

<script>
$(document).ready(function() {
    initValidate('#edit');
    initSelect2('.select2');
    initAizPlugins();

    $("#edit").submit(function(e) {
        var form = $(this);
        ajaxSubmit(e, form, callbackUpdateForm);
    });

    const callbackUpdateForm = function(response) {
        setTimeout(function() {
            location.reload();
        }, 1500);
    }
});
</script>
