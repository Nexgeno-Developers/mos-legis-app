@php
    use App\Models\Page;

    // Breadcrumb section
    $breadcrumb_image = $pageData->meta->where('meta_key', 'banner_images')->first()->meta_value ?? '';

    // Short summary section
    $short_summary_icon = $pageData->meta->where('meta_key', 'short_summary_icon')->first()->meta_value ?? '';
    $short_summary_description = $pageData->meta->where('meta_key', 'short_summary_description')->first()->meta_value ?? '';

    // Hero section
    $hero_title = $pageData->meta->where('meta_key', 'hero_title')->first()->meta_value ?? '';
    $hero_image = $pageData->meta->where('meta_key', 'hero_image')->first()->meta_value ?? '';
    $hero_description = $pageData->meta->where('meta_key', 'hero_description')->first()->meta_value ?? '';

    // Information section
    $info_title = $pageData->meta->where('meta_key', 'info_title')->first()->meta_value ?? '';
    $info_description = $pageData->meta->where('meta_key', 'info_description')->first()->meta_value ?? '';

    // Selected product categories (stored as JSON)
    $selected_product_categories = json_decode(
        $pageData->meta->where('meta_key', 'product_categories')->first()->meta_value ?? '[]',
        true
    );
    $selected_product_categories = is_array($selected_product_categories) ? $selected_product_categories : [];

    // Options for Product Categories Select2
    $layouts = ['product_category_detail_1', 'product_category_detail_2', 'product_category_detail_3', 'product_category_detail_4', 'product_category_detail_5'];

    $product_category_pagesQuery = Page::query()->whereIn('layout', $layouts);
    if (auth()->user()?->company_id) {
        $product_category_pagesQuery->where('company_id', auth()->user()->company_id);
    }
    $product_category_pages = $product_category_pagesQuery->orderBy('title')->get();

    // Video section
    $video_url = $pageData->meta->where('meta_key', 'video_url')->first()->meta_value ?? '';
@endphp

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Breadcrumb Section</h4>
    </div>
    <div class="col-md-12">
        <label for="name" class="form-label">Breadcrumb <span class="text-danger">*</span></label>
        <div class="form-group mb-2">
            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                </div>
                <div class="form-control file-amount">{{ __('Choose File') }}</div>
                <input value="{{ $breadcrumb_image }}" type="hidden" name="meta[banner_images]" class="selected-files" required>
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Short Summery Section</h4>
    </div>

    <div class="col-md-6">
        <label for="name" class="form-label">Icon <span class="text-danger">*</span></label>
        <div class="form-group mb-2">
            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                </div>
                <div class="form-control file-amount">{{ __('Choose File') }}</div>
                <input value="{{ $short_summary_icon }}" type="hidden" name="meta[short_summary_icon]" class="selected-files" required>
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div>

    <div class="col-md-6 form-group mb-2">
        <label for="short_summary_description" class="form-label">Short Description <span class="text-danger">*</span></label>
        <input
            id="short_summary_description"
            class="form-control"
            name="meta[short_summary_description]"
            type="text"
            value="{{ $short_summary_description }}"
            required
        >
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Hero Section</h4>
    </div>

    <div class="col-md-12 form-group mb-2">
        <label for="hero_title" class="form-label">Title <span class="text-danger">*</span></label>
        <input
            id="hero_title"
            class="form-control"
            name="meta[hero_title]"
            type="text"
            value="{{ $hero_title }}"
            required
        >
    </div>

    <div class="col-md-12">
        <label for="hero_image" class="form-label">Image <span class="text-danger">*</span></label>
        <div class="form-group mb-2">
            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                </div>
                <div class="form-control file-amount">{{ __('Choose File') }}</div>
                <input value="{{ $hero_image }}" type="hidden" name="meta[hero_image]" class="selected-files" required>
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div>

    <div class="col-md-12 form-group mb-2">
        <label for="hero_description" class="form-label">Description <span class="text-danger">*</span></label>
        <textarea name="meta[hero_description]" class="form-control text-editor" rows="4" required>{{ $hero_description }}</textarea>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Information Section</h4>
    </div>

    <div class="col-md-12 form-group mb-2">
        <label for="info_title" class="form-label">Title <span class="text-danger">*</span></label>
        <input
            id="info_title"
            class="form-control"
            name="meta[info_title]"
            type="text"
            value="{{ $info_title }}"
            required
        >
    </div>

    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Product Categories (Multiple Select2) <span class="text-danger">*</span></label>
        <select class="form-control select2" name="meta[product_categories][]" multiple required>
            @foreach($product_category_pages as $categoryPage)
                <option
                    value="{{ $categoryPage->id }}"
                    {{ in_array($categoryPage->id, $selected_product_categories) ? 'selected' : '' }}
                >
                    {{ $categoryPage->title }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-12 form-group mb-2">
        <label for="info_description" class="form-label">Description <span class="text-danger">*</span></label>
        <textarea name="meta[info_description]" class="form-control text-editor" rows="4" required>{{ $info_description }}</textarea>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Video Section</h4>
    </div>
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Video URL <span class="text-danger">*</span></label>
        <input type="text" name="meta[video_url]" class="form-control" value="{{ $video_url }}" placeholder="Enter video URL" required>
    </div>
</div>

