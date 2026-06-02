@php
    use App\Models\Page;

    $breadcrumb_image = $pageData->meta->where('meta_key', 'breadcrumb_image')->first()->meta_value ?? '';

    // Short Summery Section
    $short_summary_title = $pageData->meta->where('meta_key', 'short_summary_title')->first()->meta_value ?? '';
    $short_summary_image = $pageData->meta->where('meta_key', 'short_summary_image')->first()->meta_value ?? '';
    $short_summary_icon = $pageData->meta->where('meta_key', 'short_summary_icon')->first()->meta_value ?? '';
    $short_summary_description = $pageData->meta->where('meta_key', 'short_summary_description')->first()->meta_value ?? '';

    // Page Block Section
    $selected_page_blocks = json_decode(
        $pageData->meta->where('meta_key', 'page_blocks')->first()->meta_value ?? '[]',
        true
    );
    $selected_page_blocks = is_array($selected_page_blocks) ? $selected_page_blocks : [];

    $pageBlocksQuery = Page::query()->whereNotIn('layout', ['default', 'home', 'example']);
    if (auth()->user()?->company_id) {
        $pageBlocksQuery->where('company_id', auth()->user()->company_id);
    }
    $page_blocks = $pageBlocksQuery->orderBy('title')->get(['id', 'title']);

    $about_title = $pageData->meta->where('meta_key', 'about_title')->first()->meta_value ?? '';
    $about_image = $pageData->meta->where('meta_key', 'about_image')->first()->meta_value ?? '';
    $about_description = $pageData->meta->where('meta_key', 'about_description')->first()->meta_value ?? '';
    $about_video_url = $pageData->meta->where('meta_key', 'about_video_url')->first()->meta_value ?? '';

    $about_title_secondary = $pageData->meta->where('meta_key', 'about_title_secondary')->first()->meta_value ?? '';
    $about_image_secondary = $pageData->meta->where('meta_key', 'about_image_secondary')->first()->meta_value ?? '';
    $about_description_secondary = $pageData->meta->where('meta_key', 'about_description_secondary')->first()->meta_value ?? '';
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
        <h4 class="text-primary">Short Summery Section</h4>
    </div>

    <div class="col-md-4 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[short_summary_title]" value="{{ $short_summary_title }}" required>
    </div>

    <div class="col-md-4">
        <label class="form-label">Image <span class="text-danger">*</span></label>
        <div class="form-group mb-2">
            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                </div>
                <div class="form-control file-amount">{{ __('Choose File') }}</div>
                <input value="{{ $short_summary_image }}" type="hidden" name="meta[short_summary_image]" class="selected-files" required>
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div>

    <div class="col-md-4">
        <label class="form-label">Icon <span class="text-danger">*</span></label>
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

    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Short Description <span class="text-danger">*</span></label>
        <textarea name="meta[short_summary_description]" class="form-control" rows="4" required>{{ $short_summary_description }}</textarea>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Page Block Section</h4>
    </div>
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Page <span class="text-danger">*</span></label>
        <select class="form-control select2" name="meta[page_blocks][]" multiple required>
            @foreach($page_blocks as $blockPage)
                <option value="{{ $blockPage->id }}" {{ in_array($blockPage->id, $selected_page_blocks) ? 'selected' : '' }}>
                    {{ $blockPage->title }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">About Section</h4>
    </div>

    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[about_title]" value="{{ $about_title }}" required>
    </div>
    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Image <span class="text-danger">*</span></label>
        <div class="form-group mb-2">
            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                </div>
                <div class="form-control file-amount">{{ __('Choose File') }}</div>
                <input value="{{ $about_image }}" type="hidden" name="meta[about_image]" class="selected-files" required>
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div>
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Description <span class="text-danger">*</span></label>
        <textarea name="meta[about_description]" class="form-control text-editor" rows="4" required>{{ $about_description }}</textarea>
    </div>
    <div class="col-md-4 form-group mb-2">
        <label class="form-label">Video Url <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[about_video_url]" value="{{ $about_video_url }}" placeholder="Enter video URL" required>
    </div>

    <div class="col-md-4 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[about_title_secondary]" value="{{ $about_title_secondary }}" required>
    </div>
    <div class="col-md-4 form-group mb-2">
        <label class="form-label">Image <span class="text-danger">*</span></label>
        <div class="form-group mb-2">
            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                </div>
                <div class="form-control file-amount">{{ __('Choose File') }}</div>
                <input value="{{ $about_image_secondary }}" type="hidden" name="meta[about_image_secondary]" class="selected-files" required>
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div>
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Description <span class="text-danger">*</span></label>
        <textarea name="meta[about_description_secondary]" class="form-control text-editor" rows="4" required>{{ $about_description_secondary }}</textarea>
    </div>
</div>
