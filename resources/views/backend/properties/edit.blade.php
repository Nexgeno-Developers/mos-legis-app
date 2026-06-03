<form class="form" id="edit" action="{{ route($module . '.update', $property->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group mb-2">
                <label for="name" class="form-label">{{ __('labels.name') }} <span class="text-danger">*</span></label>
                <input name="name" type="text" class="form-control" minlength="3" maxlength="255" value="{{ $property->name }}" required>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="form-group mb-2">
                <label for="phone" class="form-label">{{ __('labels.phone') }} <span class="text-danger">*</span></label>
                <input name="phone" type="text" class="form-control" maxlength="20" value="{{ $property->phone }}" required>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="form-group mb-2">
                <label for="email" class="form-label">{{ __('labels.email') }} <span class="text-danger">*</span></label>
                <input name="email" type="email" class="form-control" maxlength="55" value="{{ $property->email }}" required>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="form-group mb-2">
                <label for="status" class="form-label">{{ __('labels.status') }} <span class="text-danger">*</span></label>
                <select name="status" class="form-select select2" required>
                    <option value="1" {{ $property->status ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ !$property->status ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>

        <div class="col-sm-12">
            <div class="form-group mb-2">
                <label for="address" class="form-label">{{ __('labels.address') }}</label>
                <textarea name="address" class="form-control" rows="3">{{ $property->address }}</textarea>
            </div>
        </div>

        <div class="col-sm-12">
            <div class="form-group mb-2">
                <label for="facilities" class="form-label">{{ __('labels.facilities') }}</label>
                <textarea name="facilities" class="form-control text-editor" rows="4">{{ $property->facilities }}</textarea>
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
                    <input type="hidden" name="thumbnail" class="selected-files" value="{{ $property->thumbnail }}" required>
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
                    <input type="hidden" name="images" class="selected-files" value="{{ $property->images }}" required>
                </div>
                <div class="file-preview box sm"></div>
            </div>
        </div>

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
    initTextEditor();
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
