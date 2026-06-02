@php
    $breadcrumb_image = $pageData->meta->where('meta_key', 'banner_images')->first()->meta_value ?? '';
    $hero_description = $pageData->meta->where('meta_key', 'about_description')->first()->meta_value ?? '';
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
                <input value="{{$breadcrumb_image}}" type="hidden" name="meta[banner_images]" class="selected-files" required>
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div>    
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Hero Section</h4>
    </div>      
    <div class="col-md-12 form-group mb-2">
        <label for="content" class="form-label">Description <span class="text-danger">*</span></label>
        <textarea name="meta[about_description]" class="form-control text-editor" rows="4" required>{{$hero_description}}</textarea>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <div class="text-start">
            <h4 class="text-primary mb-0">Product Categories</h4>
            <p class="mb-0">The system will automatically fetch Product Categories.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Video Section</h4>
    </div>
    <div class="col-md-12 form-group mb-2">
        <label for="content" class="form-label">Video URL <span class="text-danger">*</span></label>
        <input
            type="text"
            name="meta[video_url]"
            class="form-control"
            value="{{ $video_url }}"
            placeholder="Enter video URL"
            required
        >
    </div>
</div>

