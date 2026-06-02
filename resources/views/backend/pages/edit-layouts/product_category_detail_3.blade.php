@php
    // Breadcrumb section
    $breadcrumb_image = $pageData->meta->where('meta_key', 'banner_images')->first()->meta_value ?? '';

    // Short summary section
    $short_summary_icon = $pageData->meta->where('meta_key', 'short_summary_icon')->first()->meta_value ?? '';
    $short_summary_description = $pageData->meta->where('meta_key', 'short_summary_description')->first()->meta_value ?? '';

    // Hero section
    $hero_title = $pageData->meta->where('meta_key', 'hero_title')->first()->meta_value ?? '';
    $hero_subtitle = $pageData->meta->where('meta_key', 'hero_subtitle')->first()->meta_value ?? '';
    $hero_description = $pageData->meta->where('meta_key', 'hero_description')->first()->meta_value ?? '';

    // Products section (stored as JSON)
    $products = json_decode($pageData->meta->where('meta_key', 'products')->first()->meta_value ?? '[]', true);
    $products = is_array($products) ? $products : [];

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

    <div class="col-md-6 form-group mb-2">
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

    <div class="col-md-6 form-group mb-2">
        <label for="hero_subtitle" class="form-label">Subtitle <span class="text-danger">*</span></label>
        <input
            id="hero_subtitle"
            class="form-control"
            name="meta[hero_subtitle]"
            type="text"
            value="{{ $hero_subtitle }}"
            required
        >
    </div>

    <div class="col-md-12 form-group mb-2">
        <label for="hero_description" class="form-label">Description <span class="text-danger">*</span></label>
        <textarea
            id="hero_description"
            name="meta[hero_description]"
            class="form-control text-editor"
            rows="4"
            required
        >{{ $hero_description }}</textarea>
    </div>
</div>

{{-- <div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Products section</h4>
    </div>
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Product <span class="text-danger">*</span></label>
        <select class="form-control select2" name="meta[products][]" multiple>
            <option value="product_1" {{ in_array('product_1', $products) ? 'selected' : '' }}>Product 1</option>
            <option value="product_2" {{ in_array('product_2', $products) ? 'selected' : '' }}>Product 2</option>
            <option value="product_3" {{ in_array('product_3', $products) ? 'selected' : '' }}>Product 3</option>
            <option value="product_4" {{ in_array('product_4', $products) ? 'selected' : '' }}>Product 4</option>
            <option value="product_5" {{ in_array('product_5', $products) ? 'selected' : '' }}>Product 5</option>
        </select>
    </div>
</div> --}}

<div class="row">
    <div class="col-md-12">
        <hr>
        <div class="text-center">
            <h4 class="text-primary mb-0">Products section</h4>
            <p class="mb-0">The system will automatically fetch Products Of This Category.</p>
        </div>
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

