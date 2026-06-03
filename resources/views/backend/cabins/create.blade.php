<form class="form" id="create" action="{{ route($module . '.store') }}" method="POST">
    @csrf
    <input type="hidden" name="type" value="flexible">
    <div class="row">
        <div class="col-sm-12">
            <div class="form-group mb-2">
                <label for="property_id" class="form-label">{{ __('labels.property') }} <span class="text-danger">*</span></label>
                <select name="property_id" class="form-select select2" required>
                    <option value="">{{ __('labels.select_property') }}</option>
                    @foreach ($properties as $property)
                        <option value="{{ $property->id }}">{{ $property->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-sm-12">
            <div class="form-group mb-2">
                <label for="name" class="form-label">{{ __('labels.name') }} <span class="text-danger">*</span></label>
                <input name="name" type="text" class="form-control" minlength="3" maxlength="100" required>
            </div>
        </div>

        <div class="col-sm-12">
            <div class="form-group mb-2">
                <label for="status" class="form-label">{{ __('labels.status') }} <span class="text-danger">*</span></label>
                <select name="status" class="form-select select2" required>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
        </div>

        <div class="col-sm-12">
            <label class="form-label">{{ __('labels.thumbnail') }} <span class="text-danger">*</span></label>
            <div class="form-group mb-2">
                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                    <div class="input-group-prepend">
                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                    </div>
                    <div class="form-control file-amount">{{ __('Choose File') }}</div>
                    <input type="hidden" name="thumbnail" class="selected-files" required>
                </div>
                <div class="file-preview box sm"></div>
            </div>
        </div>

        <div class="col-sm-12">
            <label class="form-label">{{ __('labels.images') }} <span class="text-danger">*</span></label>
            <div class="form-group mb-2">
                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="true">
                    <div class="input-group-prepend">
                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                    </div>
                    <div class="form-control file-amount">{{ __('Choose File') }}</div>
                    <input type="hidden" name="images" class="selected-files" required>
                </div>
                <div class="file-preview box sm"></div>
            </div>
        </div>

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
