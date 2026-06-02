<form class="form" id="edit" action="{{ route($module . '.update', $user->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="row">
        <!-- Role -->
        <div class="col-sm-6">
            <div class="form-group mb-2">
                <label for="role_id" class="form-label">{{__('labels.role')}} <span class="text-danger">*</span></label>
                <select name="role_id" class="form-select select2" required>
                    <option value="">{{__('labels.select_role')}}</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" {{ $user->role_id == $role->id ? 'selected' : '' }}>
                            {{ ucfirst($role->name) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Name -->
        <div class="col-sm-6">
            <div class="form-group mb-2">
                <label for="name" class="form-label">{{__('labels.name')}} <span class="text-danger">*</span></label>
                <input name="name" type="text" class="form-control" minlength="3" maxlength="200" 
                       value="{{ $user->name }}" required>
            </div>
        </div>

        <!-- Email -->
        <div class="col-sm-6">
            <div class="form-group mb-2">
                <label for="email" class="form-label">{{__('labels.email')}} <span class="text-danger">*</span></label>
                <input name="email" type="email" class="form-control" value="{{ $user->email }}" required>
            </div>
        </div>

        <!-- Password (Optional) -->
        <div class="col-sm-6">
            <div class="form-group mb-2">
                <label for="password" class="form-label">{{__('labels.password_optional')}}</label>
                <input name="password" type="password" class="form-control" minlength="6">
            </div>
        </div>

        <!-- Status -->
        <div class="col-sm-12">
            <div class="form-group mb-2">
                <label for="is_active" class="form-label">{{__('labels.status')}} <span class="text-danger">*</span></label>
                <select name="is_active" class="form-select select2" required>
                    <option value="1" {{ $user->is_active ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ !$user->is_active ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="col-sm-12">
            <div class="text-center mt-1">
                <button type="submit" class="btn btn-primary">{{__('labels.update')}}</button>
            </div>
        </div>
    </div>
</form>

<!-- Initialization JS -->
<!-- <script src="{{ asset('assets/backend/js/init.js') }}"></script> -->

<script>
$(document).ready(function() {
    initValidate('#edit');
    initTextEditor();
    initSelect2('.select2');

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