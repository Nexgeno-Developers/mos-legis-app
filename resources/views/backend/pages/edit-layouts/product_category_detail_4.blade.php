@php
    // Breadcrumb section
    $breadcrumb_image = $pageData->meta->where('meta_key', 'banner_images')->first()->meta_value ?? '';

    // Short summary section
    $short_summary_icon = $pageData->meta->where('meta_key', 'short_summary_icon')->first()->meta_value ?? '';
    $short_summary_description = $pageData->meta->where('meta_key', 'short_summary_description')->first()->meta_value ?? '';

    // Hero repeater section (stored as JSON)
    $hero_items = json_decode(
        $pageData->meta->where('meta_key', 'hero_items')->first()->meta_value ?? '[]',
        true
    );
    $hero_items = is_array($hero_items) ? $hero_items : [];

    // Information repeater section (stored as JSON)
    $info_items = json_decode(
        $pageData->meta->where('meta_key', 'info_items')->first()->meta_value ?? '[]',
        true
    );
    $info_items = is_array($info_items) ? $info_items : [];

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

    <div class="hero-items-target">
        @if(isset($hero_items['itration']) && is_array($hero_items['itration']))
            @foreach($hero_items['itration'] as $index => $itration)
                <div class="row remove-parent">
                    <div class="col-md-11 mb-1">
                        <div class="row">
                            <div class="col-md-12">
                                <input value="{{ $index }}" name="meta[hero_items][itration][]" type="hidden" required>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                        </div>
                                        <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                        <input
                                            type="hidden"
                                            name="meta[hero_items][icon][]"
                                            class="selected-files"
                                            value="{{ $hero_items['icon'][$index] ?? '' }}"
                                            required
                                        >
                                    </div>
                                    <div class="file-preview box sm"></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <input
                                        value="{{ $hero_items['name'][$index] ?? '' }}"
                                        name="meta[hero_items][name][]"
                                        type="text"
                                        class="form-control"
                                        minlength="3"
                                        maxlength="200"
                                        placeholder="Enter name *"
                                        required
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-1 btn-dynamic-fields mb-1">
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
        data-limit="5"
        data-content='
            <div class="row remove-parent">
                <div class="col-md-11">
                    <div class="row">

                        <div class="col-md-12">
                            <input value="data" name="meta[hero_items][itration][]" type="hidden" required>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                    <input type="hidden" name="meta[hero_items][icon][]" class="selected-files" required>
                                </div>
                                <div class="file-preview box sm"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <input value="" name="meta[hero_items][name][]" type="text" class="form-control" minlength="3" maxlength="200" placeholder="Enter name *" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-1 btn-dynamic-fields mb-1">
                    <button type="button" class="btn btn-icon btn-circle btn-soft-danger" data-toggle="remove-parent" data-parent=".remove-parent">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
            </div>
        '
        data-target=".hero-items-target"
    >
        <i class="ti ti-plus"></i>
        <span class="ml-2">Add More</span>
    </button>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Information Section</h4>
    </div>

    <div class="info-items-target">
        @if(isset($info_items['itration']) && is_array($info_items['itration']))
            @foreach($info_items['itration'] as $index => $itration)
                <div class="row remove-parent">
                    <div class="col-md-11">
                        <div class="row">
                            <div class="col-md-12">
                                <input value="{{ $index }}" name="meta[info_items][itration][]" type="hidden" required>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <input
                                        value="{{ $info_items['title'][$index] ?? '' }}"
                                        name="meta[info_items][title][]"
                                        type="text"
                                        class="form-control"
                                        minlength="3"
                                        maxlength="200"
                                        placeholder="Enter title *"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                        </div>
                                        <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                        <input
                                            type="hidden"
                                            name="meta[info_items][image][]"
                                            class="selected-files"
                                            value="{{ $info_items['image'][$index] ?? '' }}"
                                            required
                                        >
                                    </div>
                                    <div class="file-preview box sm"></div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group mb-2">
                                    <textarea
                                        name="meta[info_items][description][]"
                                        class="form-control text-editor"
                                        rows="2"
                                        required
                                    >{{ $info_items['description'][$index] ?? '' }}</textarea>
                                </div>
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
        {{-- data-limit="5" --}}
        data-content='
            <div class="row remove-parent">
                <div class="col-md-11">
                    <div class="row">

                        <div class="col-md-12">
                            <input value="data" name="meta[info_items][itration][]" type="hidden" required>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <input value="" name="meta[info_items][title][]" type="text" class="form-control" minlength="3" maxlength="200" placeholder="Enter title *" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                    <input type="hidden" name="meta[info_items][image][]" class="selected-files" required>
                                </div>
                                <div class="file-preview box sm"></div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group mb-2">
                                <textarea name="meta[info_items][description][]" class="form-control text-editor" rows="2" placeholder="Enter description *" required></textarea>
                            </div>
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
        data-target=".info-items-target"
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
        <label class="form-label">Video URL <span class="text-danger">*</span></label>
        <input type="text" name="meta[video_url]" class="form-control" value="{{ $video_url }}" placeholder="Enter video URL" required>
    </div>
</div>

