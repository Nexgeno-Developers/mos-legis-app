@php
    // Breadcrumb section
    $breadcrumb_image = $pageData->meta->where('meta_key', 'breadcrumb_image')->first()->meta_value ?? '';

    // Short summary section
    $short_summary_image = $pageData->meta->where('meta_key', 'short_summary_image')->first()->meta_value ?? '';
    $short_summary_title = $pageData->meta->where('meta_key', 'short_summary_title')->first()->meta_value ?? '';
    $short_summary_description = $pageData->meta->where('meta_key', 'short_summary_description')->first()->meta_value ?? '';

    // Hero section
    $hero_title = $pageData->meta->where('meta_key', 'hero_title')->first()->meta_value ?? '';
    $hero_image = $pageData->meta->where('meta_key', 'hero_image')->first()->meta_value ?? '';
    $hero_navigation_url = $pageData->meta->where('meta_key', 'hero_navigation_url')->first()->meta_value ?? '';
    $hero_description = $pageData->meta->where('meta_key', 'hero_description')->first()->meta_value ?? '';

    // Highlights section (stored as JSON)
    $highlights_items = json_decode(
        $pageData->meta->where('meta_key', 'highlights_items')->first()->meta_value ?? '[]',
        true
    );
    $highlights_items = is_array($highlights_items) ? $highlights_items : [];

    // Video section
    $video_url = $pageData->meta->where('meta_key', 'video_url')->first()->meta_value ?? '';

    $brand_journey_title = $pageData->meta->where('meta_key', 'brand_journey_title')->first()->meta_value ?? '';
    // Brand Journey section (stored as JSON)
    $brand_journey_items = json_decode(
        $pageData->meta->where('meta_key', 'brand_journey_items')->first()->meta_value ?? '[]',
        true
    );
    $brand_journey_items = is_array($brand_journey_items) ? $brand_journey_items : [];
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

    <div class="col-md-6 form-group mb-2">
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

    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[short_summary_title]" value="{{ $short_summary_title }}" required>
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

    <div class="col-md-4 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[hero_title]" value="{{ $hero_title }}" required>
    </div>

    <div class="col-md-4 form-group mb-2">
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

    <div class="col-md-4 form-group mb-2">
        <label class="form-label">Navigation Url <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[hero_navigation_url]" value="{{ $hero_navigation_url }}" required>
    </div>

    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Description <span class="text-danger">*</span></label>
        <textarea name="meta[hero_description]" class="form-control text-editor" rows="4" required>{{ $hero_description }}</textarea>
    </div>
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

                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                        </div>
                                        <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                        <input
                                            type="hidden"
                                            name="meta[highlights_items][icon][]"
                                            class="selected-files"
                                            value="{{ $highlights_items['icon'][$index] ?? '' }}"
                                            required
                                        >
                                    </div>
                                    <div class="file-preview box sm"></div>
                                </div>
                            </div>

                            <div class="col-md-4">
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

                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <input
                                        value="{{ $highlights_items['description'][$index] ?? '' }}"
                                        name="meta[highlights_items][description][]"
                                        type="text"
                                        class="form-control"
                                        placeholder="Enter description *"
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
                            <input value="data" name="meta[highlights_items][itration][]" type="hidden" required>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group mb-2">
                                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                    <input type="hidden" name="meta[highlights_items][icon][]" class="selected-files" required>
                                </div>
                                <div class="file-preview box sm"></div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group mb-2">
                                <input value="" name="meta[highlights_items][title][]" type="text" class="form-control" placeholder="Enter title *" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group mb-2">
                                <input value="" name="meta[highlights_items][description][]" type="text" class="form-control" placeholder="Enter description *" required>
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

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Video Section</h4>
    </div>
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Url <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[video_url]" value="{{ $video_url }}" required>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Brand Journey Section</h4>
    </div>

    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[brand_journey_title]" value="{{ $brand_journey_title }}" required>
    </div>    

    <div class="brand-journey-items-target col-md-12">
        @if(isset($brand_journey_items['itration']) && is_array($brand_journey_items['itration']))
            @foreach($brand_journey_items['itration'] as $index => $itration)
                <div class="row remove-parent">
                    <div class="col-md-11">
                        <div class="row">
                            <div class="col-md-12">
                                <input value="{{ $index }}" name="meta[brand_journey_items][itration][]" type="hidden" required>
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
                                            name="meta[brand_journey_items][icon][]"
                                            class="selected-files"
                                            value="{{ $brand_journey_items['icon'][$index] ?? '' }}"
                                            required
                                        >
                                    </div>
                                    <div class="file-preview box sm"></div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <input
                                        value="{{ $brand_journey_items['title'][$index] ?? '' }}"
                                        name="meta[brand_journey_items][title][]"
                                        type="text"
                                        class="form-control"
                                        placeholder="Enter title *"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <input
                                        value="{{ $brand_journey_items['description'][$index] ?? '' }}"
                                        name="meta[brand_journey_items][description][]"
                                        type="text"
                                        class="form-control"
                                        placeholder="Enter description *"
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
                            <input value="data" name="meta[brand_journey_items][itration][]" type="hidden" required>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group mb-2">
                                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                    <input type="hidden" name="meta[brand_journey_items][icon][]" class="selected-files" required>
                                </div>
                                <div class="file-preview box sm"></div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group mb-2">
                                <input value="" name="meta[brand_journey_items][title][]" type="text" class="form-control" placeholder="Enter title *" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group mb-2">
                                <input value="" name="meta[brand_journey_items][description][]" type="text" class="form-control" placeholder="Enter description *" required>
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
        data-target=".brand-journey-items-target"
    >
        <i class="ti ti-plus"></i>
        <span class="ml-2">Add More</span>
    </button>
</div>

