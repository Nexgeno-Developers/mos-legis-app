<form class="form" id="edit" action="{{ route($module . '.update', $role->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row">
        <!-- Role -->
        <div class="col-sm-12">
            <div class="form-group mb-2">
                <label for="name" class="form-label">{{__("labels.name")}} <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ $role->name }}" required>
            </div>
        </div>

        <!-- Permissions -->
        <div class="col-sm-12">
            <div class="form-group">
                <label>{{__("labels.permissions")}}</label>
                <div class="row">
                    @foreach ($permissions as $module => $perms)
                        <div class="col-md-12 col-lg-12">
                            <div class="card border-1 mb-2">
                                <div class="card-header bg-light border-bottom border-dashed align-items-center fw-bold p-2 pt-1 pb-1">
                                    {{ ucfirst(str_replace('-', ' ', $module)) }}
                                </div>
                                <div class="card-body p-2">
                                    <div class="row">
                                        @foreach ($perms as $permission)
                                            @php
                                                $permissionId = str_replace(' ', '_', $permission->name);
                                            @endphp
                                            <div class="col-auto">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="{{ $permissionId }}" name="permissions[]" value="{{ $permission->name }}"
                                                        {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                                                    <label for="{{ $permissionId }}" class="form-check-label">
                                                        {{ ucfirst(Str::after($permission->name, $module . ' ')) }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>


        <!-- Submit Button -->
        <div class="col-sm-12">
            <div class="text-center mt-1">
                <button type="submit" class="btn btn-primary">{{__("labels.update")}}</button>
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
