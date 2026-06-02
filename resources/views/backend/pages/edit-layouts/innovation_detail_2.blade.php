@php
    use App\Models\Page;

    // Breadcrumb Section
    $breadcrumb_image = $pageData->meta->where('meta_key', 'breadcrumb_image')->first()->meta_value ?? '';

    // Short Summery Section
    $short_summary_icon = $pageData->meta->where('meta_key', 'short_summary_icon')->first()->meta_value ?? '';
    $short_summary_description = $pageData->meta->where('meta_key', 'short_summary_description')->first()->meta_value ?? '';

    // Hero Section
    $hero_title = $pageData->meta->where('meta_key', 'hero_title')->first()->meta_value ?? '';
    $hero_subtitle = $pageData->meta->where('meta_key', 'hero_subtitle')->first()->meta_value ?? '';
    $hero_description = $pageData->meta->where('meta_key', 'hero_description')->first()->meta_value ?? '';
    $hero_image = $pageData->meta->where('meta_key', 'hero_image')->first()->meta_value ?? '';
    $hero_capabilities_navigation_link = $pageData->meta->where('meta_key', 'hero_capabilities_navigation_link')->first()->meta_value ?? '';
    $hero_specs_navigation_link = $pageData->meta->where('meta_key', 'hero_specs_navigation_link')->first()->meta_value ?? '';

    // Pilot Plant Section
    $pilot_plant_title = $pageData->meta->where('meta_key', 'pilot_plant_title')->first()->meta_value ?? '';
    $pilot_plant_description = $pageData->meta->where('meta_key', 'pilot_plant_description')->first()->meta_value ?? '';
    $selected_pilot_plant_pages = json_decode(
        $pageData->meta->where('meta_key', 'pilot_plant_pages')->first()->meta_value ?? '[]',
        true
    );
    $selected_pilot_plant_pages = is_array($selected_pilot_plant_pages) ? $selected_pilot_plant_pages : [];

    $pilotPlantPagesQuery = Page::query()
        ->where('id', '!=', $pageData->id)
        ->whereNotIn('layout', ['default', 'home', 'example']);

    if (auth()->user()?->company_id) {
        $pilotPlantPagesQuery->where('company_id', auth()->user()->company_id);
    }

    $pilot_plant_pages = $pilotPlantPagesQuery->orderBy('title')->get(['id', 'title']);

    // Application Versatility Section
    $application_versatility_title = $pageData->meta->where('meta_key', 'application_versatility_title')->first()->meta_value ?? '';
    $application_versatility_subtitle = $pageData->meta->where('meta_key', 'application_versatility_subtitle')->first()->meta_value ?? '';
    $application_versatility_description = $pageData->meta->where('meta_key', 'application_versatility_description')->first()->meta_value ?? '';

    $selected_application_versatility_product_industries = json_decode(
        $pageData->meta->where('meta_key', 'application_versatility_product_industries')->first()->meta_value ?? '[]',
        true
    );
    $selected_application_versatility_product_industries = is_array($selected_application_versatility_product_industries) ? $selected_application_versatility_product_industries : [];

    $productIndustryQuery = Page::query()->where('layout', 'product_industry_detail');
    if (auth()->user()?->company_id) {
        $productIndustryQuery->where('company_id', auth()->user()->company_id);
    }
    $product_industry_pages = $productIndustryQuery->orderBy('title')->get(['id', 'title']);

    $application_versatility_items = json_decode(
        $pageData->meta->where('meta_key', 'application_versatility_items')->first()->meta_value ?? '[]',
        true
    );
    $application_versatility_items = is_array($application_versatility_items) ? $application_versatility_items : [];

    // Ecosystem Section
    $ecosystem_items = json_decode(
        $pageData->meta->where('meta_key', 'ecosystem_items')->first()->meta_value ?? '[]',
        true
    );
    $ecosystem_items = is_array($ecosystem_items) ? $ecosystem_items : [];

    // Highlights Section
    $highlights_items = json_decode(
        $pageData->meta->where('meta_key', 'highlights_items')->first()->meta_value ?? '[]',
        true
    );
    $highlights_items = is_array($highlights_items) ? $highlights_items : [];
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

    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Short Description <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[short_summary_description]" value="{{ $short_summary_description }}" required>
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
        <label class="form-label">Subtitle <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[hero_subtitle]" value="{{ $hero_subtitle }}" required>
    </div>

    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Description <span class="text-danger">*</span></label>
        <textarea name="meta[hero_description]" class="form-control text-editor" rows="4" required>{{ $hero_description }}</textarea>
    </div>

    <div class="col-md-12">
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

    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Capebilities Navigation Link <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[hero_capabilities_navigation_link]" value="{{ $hero_capabilities_navigation_link }}" required>
    </div>

    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Specs Navigation Link <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[hero_specs_navigation_link]" value="{{ $hero_specs_navigation_link }}" required>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Pilot Plant Section</h4>
    </div>

    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[pilot_plant_title]" value="{{ $pilot_plant_title }}" required>
    </div>

    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Description <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[pilot_plant_description]" value="{{ $pilot_plant_description }}" required>
    </div>

    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Page <span class="text-danger">*</span></label>
        <select class="form-control select2" name="meta[pilot_plant_pages][]" multiple required>
            @foreach($pilot_plant_pages as $blockPage)
                <option value="{{ $blockPage->id }}" {{ in_array($blockPage->id, $selected_pilot_plant_pages) ? 'selected' : '' }}>
                    {{ $blockPage->title }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Application Versatility Section</h4>
    </div>

    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[application_versatility_title]" value="{{ $application_versatility_title }}" required>
    </div>

    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Subtitle <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[application_versatility_subtitle]" value="{{ $application_versatility_subtitle }}" required>
    </div>

    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Description <span class="text-danger">*</span></label>
        <textarea name="meta[application_versatility_description]" class="form-control text-editor" rows="4" required>{{ $application_versatility_description }}</textarea>
    </div>

    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Product Industry <span class="text-danger">*</span></label>
        <select class="form-control select2" name="meta[application_versatility_product_industries][]" multiple required>
            @foreach($product_industry_pages as $row)
                <option value="{{ $row->id }}" {{ in_array($row->id, $selected_application_versatility_product_industries) ? 'selected' : '' }}>
                    {{ $row->title }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="application-versatility-items-target col-md-12 d-none">
        @if(isset($application_versatility_items['itration']) && is_array($application_versatility_items['itration']))
            @foreach($application_versatility_items['itration'] as $index => $itration)
                <div class="row remove-parent">
                    <div class="col-md-11">
                        <div class="row">
                            <div class="col-md-12">
                                <input value="{{ $index }}" name="meta[application_versatility_items][itration][]" type="hidden">
                            </div>

                            <div class="col-md-4 form-group mb-2">
                                <input
                                    type="text"
                                    name="meta[application_versatility_items][title][]"
                                    class="form-control"
                                    value="{{ $application_versatility_items['title'][$index] ?? '' }}"
                                    placeholder="Title *"
                                >
                            </div>

                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                        </div>
                                        <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                        <input
                                            type="hidden"
                                            name="meta[application_versatility_items][icon][]"
                                            class="selected-files"
                                            value="{{ $application_versatility_items['icon'][$index] ?? '' }}"
                                        >
                                    </div>
                                    <div class="file-preview box sm"></div>
                                </div>
                            </div>

                            <div class="col-md-4 form-group mb-2">
                                <textarea
                                    name="meta[application_versatility_items][description][]"
                                    class="form-control"
                                    rows="3"
                                    placeholder="Description *"
                                    required
                                >{{ $application_versatility_items['description'][$index] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1 btn-dynamic-fields">
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
        class="mt-1 btn btn-soft-success btn-icon w-100 d-none"
        data-toggle="add-more"
        data-limit="10"
        data-content='
            <div class="row remove-parent">
                <div class="col-md-11">
                    <div class="row">
                        <div class="col-md-12">
                            <input value="data" name="meta[application_versatility_items][itration][]" type="hidden" required>
                        </div>
                        <div class="col-md-4 form-group mb-2">
                            <input type="text" name="meta[application_versatility_items][title][]" class="form-control" placeholder="Title *" required>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-2">
                                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                    <input type="hidden" name="meta[application_versatility_items][icon][]" class="selected-files" required>
                                </div>
                                <div class="file-preview box sm"></div>
                            </div>
                        </div>
                        <div class="col-md-4 form-group mb-2">
                            <textarea name="meta[application_versatility_items][description][]" class="form-control" rows="3" placeholder="Description *" required></textarea>
                        </div>
                    </div>
                </div>
                <div class="col-md-1 btn-dynamic-fields">
                    <button type="button" class="btn btn-icon btn-circle btn-soft-danger" data-toggle="remove-parent" data-parent=".remove-parent">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
            </div>
        '
        data-target=".application-versatility-items-target"
    >
        <i class="ti ti-plus"></i>
        <span class="ml-2">Add More</span>
    </button>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Ecosystem Section</h4>
    </div>

    <div class="ecosystem-items-target col-md-12">
        @if(isset($ecosystem_items['itration']) && is_array($ecosystem_items['itration']))
            @foreach($ecosystem_items['itration'] as $index => $itration)
                <div class="row remove-parent">
                    <div class="col-md-11">
                        <div class="row">
                            <div class="col-md-12">
                                <input value="{{ $index }}" name="meta[ecosystem_items][itration][]" type="hidden" required>
                            </div>

                            <div class="col-md-3 form-group mb-2">
                                <input type="text" name="meta[ecosystem_items][title][]" class="form-control" value="{{ $ecosystem_items['title'][$index] ?? '' }}" placeholder="Title *" required>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group mb-2">
                                    <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                        </div>
                                        <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                        <input type="hidden" name="meta[ecosystem_items][image][]" class="selected-files" value="{{ $ecosystem_items['image'][$index] ?? '' }}" required>
                                    </div>
                                    <div class="file-preview box sm"></div>
                                </div>
                            </div>

                            <div class="col-md-3 form-group mb-2">
                                <input type="text" name="meta[ecosystem_items][point][]" class="form-control" value="{{ $ecosystem_items['point'][$index] ?? '' }}" placeholder="Point *" required>
                            </div>

                            <div class="col-md-3 form-group mb-2">
                                <input type="text" name="meta[ecosystem_items][value][]" class="form-control" value="{{ $ecosystem_items['value'][$index] ?? '' }}" placeholder="Value *" required>
                            </div>

                            <div class="col-md-12 form-group mb-2">
                                <textarea name="meta[ecosystem_items][description][]" class="form-control" rows="3" placeholder="Description *" required>{{ $ecosystem_items['description'][$index] ?? '' }}</textarea>
                            </div>

                        </div>
                    </div>
                    <div class="col-md-1 btn-dynamic-fields">
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
        data-limit="10"
        data-content='
            <div class="row remove-parent">
                <div class="col-md-11">
                    <div class="row">
                        <div class="col-md-12">
                            <input value="data" name="meta[ecosystem_items][itration][]" type="hidden" required>
                        </div>
                        <div class="col-md-3 form-group mb-2">
                            <input type="text" name="meta[ecosystem_items][title][]" class="form-control" placeholder="Title *" required>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                    <input type="hidden" name="meta[ecosystem_items][image][]" class="selected-files" required>
                                </div>
                                <div class="file-preview box sm"></div>
                            </div>
                        </div>
                        <div class="col-md-3 form-group mb-2">
                            <input type="text" name="meta[ecosystem_items][point][]" class="form-control" placeholder="Point *" required>
                        </div>
                        <div class="col-md-3 form-group mb-2">
                            <input type="text" name="meta[ecosystem_items][value][]" class="form-control" placeholder="Value *" required>
                        </div>
                        <div class="col-md-12 form-group mb-2">
                            <textarea name="meta[ecosystem_items][description][]" class="form-control" rows="3" placeholder="Description *" required></textarea>
                        </div>
                    </div>
                </div>
                <div class="col-md-1 btn-dynamic-fields">
                    <button type="button" class="btn btn-icon btn-circle btn-soft-danger" data-toggle="remove-parent" data-parent=".remove-parent">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
            </div>
        '
        data-target=".ecosystem-items-target"
    >
        <i class="ti ti-plus"></i>
        <span class="ml-2">Add More</span>
    </button>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Highlights Section</h4>
    </div>

    <div class="highlights-items-target col-md-12">
        @if(isset($highlights_items['itration']) && is_array($highlights_items['itration']))
            @foreach($highlights_items['itration'] as $index => $itration)
                <div class="row remove-parent">
                    <div class="col-md-11">
                        <div class="row">
                            <div class="col-md-12">
                                <input value="{{ $index }}" name="meta[highlights_items][itration][]" type="hidden" required>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <input
                                        value="{{ $highlights_items['value'][$index] ?? '' }}"
                                        name="meta[highlights_items][value][]"
                                        type="text"
                                        class="form-control"
                                        placeholder="Enter value *"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <input
                                        value="{{ $highlights_items['title'][$index] ?? '' }}"
                                        name="meta[highlights_items][title][]"
                                        type="text"
                                        class="form-control"
                                        placeholder="Enter title *"
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
        data-limit="10"
        data-content='
            <div class="row remove-parent">
                <div class="col-md-11">
                    <div class="row">
                        <div class="col-md-12">
                            <input value="data" name="meta[highlights_items][itration][]" type="hidden" required>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <input value="" name="meta[highlights_items][value][]" type="text" class="form-control" placeholder="Enter value *" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <input value="" name="meta[highlights_items][title][]" type="text" class="form-control" placeholder="Enter title *" required>
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
        data-target=".highlights-items-target"
    >
        <i class="ti ti-plus"></i>
        <span class="ml-2">Add More</span>
    </button>
</div>
