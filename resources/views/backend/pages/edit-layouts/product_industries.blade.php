@php
    // Breadcrumb section
    $breadcrumb_image = $pageData->meta->where('meta_key', 'breadcrumb_image')->first()->meta_value ?? '';
    $breadcrumb_subtitle = $pageData->meta->where('meta_key', 'breadcrumb_subtitle')->first()->meta_value ?? '';

    // Industries Segments section
    $industries_segments_title = $pageData->meta->where('meta_key', 'industries_segments_title')->first()->meta_value ?? '';

    // Insights section
    $insights_title = $pageData->meta->where('meta_key', 'insights_title')->first()->meta_value ?? '';
    $insights_subtitle = $pageData->meta->where('meta_key', 'insights_subtitle')->first()->meta_value ?? '';
    $insights_image = $pageData->meta->where('meta_key', 'insights_image')->first()->meta_value ?? '';
    $insights_navigation_url = $pageData->meta->where('meta_key', 'insights_navigation_url')->first()->meta_value ?? '';
    $insights_description = $pageData->meta->where('meta_key', 'insights_description')->first()->meta_value ?? '';
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

    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Subtitle <span class="text-danger">*</span></label>
        <textarea name="meta[breadcrumb_subtitle]" class="form-control" rows="4" required>{{ $breadcrumb_subtitle }}</textarea>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <div class="text-primary">
            <h4 class="text-primary mb-0">Industries Segments Section</h4>
        </div>

        <div class="col-md-12 form-group mb-2">
            <label class="form-label">Title <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="meta[industries_segments_title]" value="{{ $industries_segments_title }}" required>
        </div>

        <div class="text-center mt-2">
            <p class="mb-0">The system will automatically fetch Product Industries.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Insights Section</h4>
    </div>

    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[insights_title]" value="{{ $insights_title }}" required>
    </div>

    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Subtitle <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[insights_subtitle]" value="{{ $insights_subtitle }}" required>
    </div>

    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Image <span class="text-danger">*</span></label>
        <div class="form-group mb-2">
            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                </div>
                <div class="form-control file-amount">{{ __('Choose File') }}</div>
                <input value="{{ $insights_image }}" type="hidden" name="meta[insights_image]" class="selected-files" required>
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div>

    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Navigation Url <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[insights_navigation_url]" value="{{ $insights_navigation_url }}" required>
    </div>

    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Description <span class="text-danger">*</span></label>
        <textarea name="meta[insights_description]" class="form-control" rows="4" required>{{ $insights_description }}</textarea>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <div class="text-center">
            <h4 class="text-primary mb-0">Featured Products Section</h4>
            <p class="mb-0">The system will automatically fetch marked featured products.</p>
        </div>
    </div>
</div>
