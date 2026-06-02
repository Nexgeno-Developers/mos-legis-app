<form id="edit" action="{{ route('authors.update', $pageData->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-sm-12">
            <div class="form-group mb-2">
                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                <input value="{{ old('name', $pageData->name) }}" name="name" type="text" class="form-control" minlength="3" maxlength="200" required>
            </div>
        </div>

        <div class="col-sm-12">
            <div class="form-group mb-2">
                <label for="email" class="form-label">Email</label>
                <input value="{{ old('email', $pageData->email) }}" name="email" type="email" class="form-control" maxlength="255">
            </div>
        </div>

        <div class="col-sm-12">
            <div class="form-group mb-2">
                <label for="bio" class="form-label">Bio</label>
                <textarea name="bio" class="form-control" rows="3">{{ old('bio', $pageData->bio) }}</textarea>
            </div>
        </div>

        <div class="col-sm-12">
            <div class="form-group mb-2">
                <label for="profile_image" class="form-label">Profile Image</label>
                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                    <div class="input-group-prepend">
                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                    </div>
                    <div class="form-control file-amount">{{ __('Choose File') }}</div>
                    <input type="hidden" name="profile_image" class="selected-files" value="{{ $pageData->profile_image }}">
                </div>
                <div class="file-preview box sm"></div>
            </div>
        </div>

        <div class="col-sm-12">
            <div class="form-group mb-2">
                <label for="is_active" class="form-label">Status</label>
                <select name="is_active" class="form-select" required>
                    <option value="1" @if(old('is_active', $pageData->is_active) == 1) selected @endif>Active</option>
                    <option value="0" @if(old('is_active', $pageData->is_active) == 0) selected @endif>Inactive</option>
                </select>
            </div>
        </div>

        <div class="col-sm-12">
            <div class="text-center mt-1">
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </div>
    </div>
</form>

<script>
$(document).ready(function() {
    initValidate('#edit');
    initAizPlugins();
    initOtherPlugins();

    $("#edit").submit(function(e) {
        var form = $(this);
        ajaxSubmit(e, form, callbackEditForm);
    });

    const callbackEditForm = function(response) {
        setTimeout(function() {
            location.reload();
        }, 1500);
    }
});
</script>
