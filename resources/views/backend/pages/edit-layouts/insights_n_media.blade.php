@php
    use App\Models\Category;

    // Breadcrumb Section
    $breadcrumb_image = $pageData->meta->where('meta_key', 'breadcrumb_image')->first()->meta_value ?? '';

    // Post Block Section (stored as JSON)
    $selected_post_block_categories = json_decode(
        $pageData->meta->where('meta_key', 'post_block_categories')->first()->meta_value ?? '[]',
        true
    );
    $selected_post_block_categories = is_array($selected_post_block_categories) ? $selected_post_block_categories : [];

    $categoryQuery = Category::query()->whereNull('parent_id');
    if (auth()->user()?->company_id) {
        $categoryQuery->where('company_id', auth()->user()->company_id);
    }
    $post_block_categories = $categoryQuery->orderBy('name')->get(['id', 'name']);
@endphp

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Breadcrumb Section</h4>
    </div>
    <div class="col-md-12">
        <label class="form-label">Breadcrumb Image <span class="text-danger">*</span></label>
        <div class="form-group mb-2">
            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                </div>
                <div class="form-control file-amount">{{ __('Choose File') }}</div>
                <input value="{{ $breadcrumb_image }}" type="hidden" name="meta[breadcrumb_image]" class="selected-files" required>
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Post Block Section</h4>
    </div>

    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Page <span class="text-danger">*</span></label>
        <select class="form-control select2" name="meta[post_block_categories][]" multiple required>
            @foreach($post_block_categories as $category)
                <option value="{{ $category->id }}" {{ in_array($category->id, $selected_post_block_categories) ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
    </div>
</div>
