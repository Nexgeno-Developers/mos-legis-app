@php
    use App\Models\Page;

    // Breadcrumb Section
    $breadcrumb_image = $pageData->meta->where('meta_key', 'breadcrumb_image')->first()->meta_value ?? '';
    $breadcrumb_title = $pageData->meta->where('meta_key', 'breadcrumb_title')->first()->meta_value ?? '';

    // Hero Section
    $hero_title = $pageData->meta->where('meta_key', 'hero_title')->first()->meta_value ?? '';
    $hero_description = $pageData->meta->where('meta_key', 'hero_description')->first()->meta_value ?? '';
    $hero_image = $pageData->meta->where('meta_key', 'hero_image')->first()->meta_value ?? '';

    // Page Block Section
    $selected_page_blocks = json_decode(
        $pageData->meta->where('meta_key', 'page_blocks')->first()->meta_value ?? '[]',
        true
    );
    $selected_page_blocks = is_array($selected_page_blocks) ? $selected_page_blocks : [];

    $pageBlocksQuery = Page::query()
        ->where('id', '!=', $pageData->id)
        ->whereNotIn('layout', ['default', 'home', 'example']);

    if (auth()->user()?->company_id) {
        $pageBlocksQuery->where('company_id', auth()->user()->company_id);
    }

    $page_blocks = $pageBlocksQuery->orderBy('title')->get(['id', 'title']);
@endphp

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Breadcrumb Section</h4>
    </div>

    <div class="col-md-6">
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

    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[breadcrumb_title]" value="{{ $breadcrumb_title }}" required>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Hero Section</h4>
    </div>

    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[hero_title]" value="{{ $hero_title }}" required>
    </div>

    <div class="col-md-6">
        <label class="form-label">Image <span class="text-danger">*</span></label>
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
        <label class="form-label">Description <span class="text-danger">*</span></label>
        <textarea name="meta[hero_description]" class="form-control" rows="4" required>{{ $hero_description }}</textarea>
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
        <div class="text-center">
            <h4 class="text-primary mb-0">Latest News Section</h4>
            <p class="mb-0">The system will automatically fetch and display all lastest News, if available.</p>
        </div>
    </div>
</div>
