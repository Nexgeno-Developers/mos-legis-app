<form id="create" action="{{ route('blog-categories.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="row">

        <div class="col-sm-12">
            <div class="form-group mb-2">
                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                <input value="" name="name" type="text" class="form-control" minlength="3" maxlength="200" required>
            </div>
        </div>

        <div class="col-sm-12">
            <div class="form-group mb-2">
                <label for="slug" class="form-label">Slug <span class="text-danger">*</span></label>
                <input value="" name="slug" type="text" class="form-control" maxlength="200" required>
            </div>
        </div>

        <div class="col-sm-12">
            <div class="form-group mb-2">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>
        </div>

        <div class="col-sm-12">
            <div class="form-group mb-2">
                <label for="meta_title" class="form-label">Meta Title</label>
                <input value="" name="meta_title" type="text" class="form-control" maxlength="255">
            </div>
        </div>

        <div class="col-sm-12">
            <div class="form-group mb-2">
                <label for="meta_description" class="form-label">Meta Description</label>
                <textarea name="meta_description" class="form-control" rows="3" maxlength="500"></textarea>
            </div>
        </div>

        <div class="col-sm-12">
            <div class="form-group mb-2">
                <label for="is_active" class="form-label">Status</label>
                <select name="is_active" class="form-select" required>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
        </div>

        <div class="col-sm-12">
            <div class="text-center mt-1">
                <button type="submit" class="btn btn-primary">Create</button>
            </div>
        </div>
    </div>
</form>

<script>
$(document).ready(function() {
    initValidate('#create');
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
