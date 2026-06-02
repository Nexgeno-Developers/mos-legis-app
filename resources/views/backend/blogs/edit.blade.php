<form id="edit" action="{{ route('blogs.update', $pageData->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="row">

        <div class="col-sm-12">
            <div class="form-group mb-2">
                <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                <input value="{{ old('title', $pageData->title) }}" name="title" type="text" class="form-control" minlength="3" maxlength="255" required>
            </div>
        </div>

        <div class="col-sm-12">
            <div class="form-group mb-2">
                <label for="slug" class="form-label">Slug <span class="text-danger">*</span></label>
                <input value="{{ old('slug', $pageData->slug) }}" name="slug" type="text" class="form-control" maxlength="255" required>
            </div>
        </div>

        <div class="col-sm-12">
            <div class="form-group mb-2">
                <label for="category_ids" class="form-label">Categories <span class="text-danger">*</span></label>
                <select name="category_ids[]" class="form-select select2" multiple required>
                    @foreach ($categoryList as $index => $row)
                        <option value="{{ $row->id }}" @if(in_array($row->id, $selectedCategoryIds ?? [])) selected @endif>{{ $row->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-sm-12">
            <div class="form-group mb-2">
                <label for="excerpt" class="form-label">Excerpt</label>
                <textarea name="excerpt" class="form-control" rows="2">{{ old('excerpt', $pageData->excerpt) }}</textarea>
            </div>
        </div>

        <div class="col-sm-12">
            <div class="form-group mb-2">
                <label for="content" class="form-label">Content</label>
                <textarea name="content" class="form-control text-editor" rows="4">{{ old('content', $pageData->content) }}</textarea>
            </div>
        </div>

        <div class="col-sm-12">
            <div class="form-group mb-2">
                <label for="image" class="form-label">Image</label>
                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                    <div class="input-group-prepend">
                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                    </div>
                    <div class="form-control file-amount">{{ __('Choose File') }}</div>
                    <input type="hidden" name="image" class="selected-files" value="{{ $pageData->image }}">
                </div>
                <div class="file-preview box sm"></div>
            </div>
        </div>

        <div class="col-sm-12">
            <div class="form-group mb-2">
                <label for="published_at" class="form-label">Published At</label>
                @php
                    $publishedAtValue = $pageData->published_at ? \Carbon\Carbon::parse($pageData->published_at)->format('Y-m-d\\TH:i') : '';
                @endphp
                <input value="{{ old('published_at', $publishedAtValue) }}" name="published_at" type="datetime-local" class="form-control">
            </div>
        </div>

        <div class="col-sm-12">
            <div class="form-group mb-2">
                <label for="seo_title" class="form-label">Meta Title</label>
                <input value="{{ old('seo_title', $pageData->seo_title) }}" name="seo_title" type="text" class="form-control" maxlength="255">
            </div>
        </div>

        <div class="col-sm-12">
            <div class="form-group mb-2">
                <label for="seo_description" class="form-label">Meta Description</label>
                <textarea name="seo_description" class="form-control" rows="3" maxlength="500">{{ old('seo_description', $pageData->seo_description) }}</textarea>
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
    initTextEditor();
    initSelect2('.select2');
    AIZ.uploader.previewGenerate();

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
