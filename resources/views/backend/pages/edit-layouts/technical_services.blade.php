@php
    use App\Models\Page;

    // Breadcrumb section
    $breadcrumb_image = $pageData->meta->where('meta_key', 'breadcrumb_image')->first()->meta_value ?? '';

    // Short summary section
    $short_summary_title = $pageData->meta->where('meta_key', 'short_summary_title')->first()->meta_value ?? '';
    $short_summary_icon = $pageData->meta->where('meta_key', 'short_summary_icon')->first()->meta_value ?? '';
    $short_summary_image = $pageData->meta->where('meta_key', 'short_summary_image')->first()->meta_value ?? '';
    $short_summary_description = $pageData->meta->where('meta_key', 'short_summary_description')->first()->meta_value ?? '';

    // Hero section
    $hero_title = $pageData->meta->where('meta_key', 'hero_title')->first()->meta_value ?? '';
    $hero_image = $pageData->meta->where('meta_key', 'hero_image')->first()->meta_value ?? '';
    $hero_description = $pageData->meta->where('meta_key', 'hero_description')->first()->meta_value ?? '';

    // Upgrade & Expand section
    $upgrade_expand_title = $pageData->meta->where('meta_key', 'upgrade_expand_title')->first()->meta_value ?? '';

    // Differentiation section (stored as JSON)
    $differentiation_title = $pageData->meta->where('meta_key', 'differentiation_title')->first()->meta_value ?? '';
    $differentiation_items = json_decode(
        $pageData->meta->where('meta_key', 'differentiation_items')->first()->meta_value ?? '[]',
        true
    );
    $differentiation_items = is_array($differentiation_items) ? $differentiation_items : [];

    // Operational section
    $operational_title = $pageData->meta->where('meta_key', 'operational_title')->first()->meta_value ?? '';
    $selected_page_blocks = json_decode(
        $pageData->meta->where('meta_key', 'page_blocks')->first()->meta_value ?? '[]',
        true
    );
    $selected_page_blocks = is_array($selected_page_blocks) ? $selected_page_blocks : [];

    $pageBlocksQuery = Page::query()->where('id', '!=', $pageData->id);
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

    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[short_summary_title]" value="{{ $short_summary_title }}" required>
    </div>

    <div class="col-md-6">
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

    <div class="col-md-6">
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

    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Short Description <span class="text-danger">*</span></label>
        <textarea name="meta[short_summary_description]" class="form-control" rows="4" required>{{ $short_summary_description }}</textarea>
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

    <div class="col-md-6 form-group mb-2">
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
        <textarea name="meta[hero_description]" class="form-control text-editor" rows="4" required>{{ $hero_description }}</textarea>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Upgrade & Expand Section</h4>
    </div>

    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[upgrade_expand_title]" value="{{ $upgrade_expand_title }}" required>
    </div>

    <div class="col-md-12">
        <div class="text-center">
            <p class="mb-0">The system will automatically fetch and display all The Technical Service Detail Blocks. if available.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Differentiation Section</h4>
    </div>

    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[differentiation_title]" value="{{ $differentiation_title }}" required>
    </div>

    <div class="differentiation-items-target col-md-12">
        @if(isset($differentiation_items['itration']) && is_array($differentiation_items['itration']))
            @foreach($differentiation_items['itration'] as $index => $itration)
                <div class="row remove-parent">
                    <div class="col-md-11">
                        <div class="row">
                            <div class="col-md-12">
                                <input value="{{ $index }}" name="meta[differentiation_items][itration][]" type="hidden" required>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group mb-2">
                                    <input
                                        value="{{ $differentiation_items['row_1'][$index] ?? '' }}"
                                        name="meta[differentiation_items][row_1][]"
                                        type="text"
                                        class="form-control"
                                        placeholder="Title *"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group mb-2">
                                    <input
                                        value="{{ $differentiation_items['row_2'][$index] ?? '' }}"
                                        name="meta[differentiation_items][row_2][]"
                                        type="text"
                                        class="form-control"
                                        placeholder="Title *"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group mb-2">
                                    <input
                                        value="{{ $differentiation_items['title_1'][$index] ?? '' }}"
                                        name="meta[differentiation_items][title_1][]"
                                        type="text"
                                        class="form-control"
                                        placeholder="Title *"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group mb-2">
                                    <input
                                        value="{{ $differentiation_items['title_2'][$index] ?? '' }}"
                                        name="meta[differentiation_items][title_2][]"
                                        type="text"
                                        class="form-control"
                                        placeholder="Title *"
                                        required
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-1 btn-dynamic-fields mb-2">
                        <button type="button" class="btn btn-icon btn-circle btn-soft-danger" data-toggle="remove-parent" data-parent=".remove-parent">
                            <i class="ti ti-x"></i>
                        </button>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <button
        type="button"
        class="mt-1 btn btn-soft-success btn-icon w-100"
        data-toggle="add-more"
        data-limit="6"
        data-content='
            <div class="row remove-parent">
                <div class="col-md-11">
                    <div class="row">
                        <div class="col-md-12">
                            <input value="data" name="meta[differentiation_items][itration][]" type="hidden" required>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <input value="" name="meta[differentiation_items][row_1][]" type="text" class="form-control" placeholder="Title *" required>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <input value="" name="meta[differentiation_items][row_2][]" type="text" class="form-control" placeholder="Title *" required>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <input value="" name="meta[differentiation_items][title_1][]" type="text" class="form-control" placeholder="Title *" required>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <input value="" name="meta[differentiation_items][title_2][]" type="text" class="form-control" placeholder="Title *" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-1 btn-dynamic-fields mb-2">
                    <button type="button" class="btn btn-icon btn-circle btn-soft-danger" data-toggle="remove-parent" data-parent=".remove-parent">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
            </div>
        '
        data-target=".differentiation-items-target"
    >
        <i class="ti ti-plus"></i>
        <span class="ml-2">Add More</span>
    </button>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Operational Section</h4>
    </div>

    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[operational_title]" value="{{ $operational_title }}" required>
    </div>

    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Page Blocks <span class="text-danger">*</span></label>
        <select class="form-control select2" name="meta[page_blocks][]" multiple required>
            @foreach($page_blocks as $blockPage)
                <option value="{{ $blockPage->id }}" {{ in_array($blockPage->id, $selected_page_blocks) ? 'selected' : '' }}>
                    {{ $blockPage->title }}
                </option>
            @endforeach
        </select>
    </div>
</div>

