@php
    // Breadcrumb Section
    $breadcrumb_image = $pageData->meta->where('meta_key', 'breadcrumb_image')->first()->meta_value ?? '';

    // Short Summery Section
    $short_summary_image = $pageData->meta->where('meta_key', 'short_summary_image')->first()->meta_value ?? '';
    $short_summary_description = $pageData->meta->where('meta_key', 'short_summary_description')->first()->meta_value ?? '';

    // Hero Section
    $hero_title = $pageData->meta->where('meta_key', 'hero_title')->first()->meta_value ?? '';
    $hero_items = json_decode($pageData->meta->where('meta_key', 'hero_items')->first()->meta_value ?? '[]', true);
    $hero_items = is_array($hero_items) ? $hero_items : [];

    // Sustainable Packaging Vision Section
    $sustainable_packaging_vision_image = $pageData->meta->where('meta_key', 'sustainable_packaging_vision_image')->first()->meta_value ?? '';
    $sustainable_packaging_vision_title = $pageData->meta->where('meta_key', 'sustainable_packaging_vision_title')->first()->meta_value ?? '';

    // Why Carton Packaging Matters Section
    $why_carton_title = $pageData->meta->where('meta_key', 'why_carton_title')->first()->meta_value ?? '';
    $why_carton_description = $pageData->meta->where('meta_key', 'why_carton_description')->first()->meta_value ?? '';
    $why_carton_items = json_decode($pageData->meta->where('meta_key', 'why_carton_items')->first()->meta_value ?? '[]', true);
    $why_carton_items = is_array($why_carton_items) ? $why_carton_items : [];

    // Lamipak Sustainability Commitment Section
    $lamipak_commitment_image = $pageData->meta->where('meta_key', 'lamipak_commitment_image')->first()->meta_value ?? '';
    $lamipak_commitment_title = $pageData->meta->where('meta_key', 'lamipak_commitment_title')->first()->meta_value ?? '';

    // Carton Packaging Impact & Statistics Section
    $impact_statistics_title = $pageData->meta->where('meta_key', 'impact_statistics_title')->first()->meta_value ?? '';
    $impact_statistics_description = $pageData->meta->where('meta_key', 'impact_statistics_description')->first()->meta_value ?? '';
    $impact_statistics_items = json_decode($pageData->meta->where('meta_key', 'impact_statistics_items')->first()->meta_value ?? '[]', true);
    $impact_statistics_items = is_array($impact_statistics_items) ? $impact_statistics_items : [];
    $impact_statistics_footer_description = $pageData->meta->where('meta_key', 'impact_statistics_footer_description')->first()->meta_value ?? '';

    // Recycling Journey Visual Section
    $recycling_journey_image = $pageData->meta->where('meta_key', 'recycling_journey_image')->first()->meta_value ?? '';
    $recycling_journey_description = $pageData->meta->where('meta_key', 'recycling_journey_description')->first()->meta_value ?? '';
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
        <label class="form-label">Short Description <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[short_summary_description]" value="{{ $short_summary_description }}" required>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Hero Section</h4>
    </div>
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[hero_title]" value="{{ $hero_title }}" required>
    </div>

    <div class="hero-items-target col-md-12">
        @if(isset($hero_items['itration']) && is_array($hero_items['itration']))
            @foreach($hero_items['itration'] as $index => $itration)
                <div class="row remove-parent">
                    <div class="col-md-11">
                        <div class="row">
                            <div class="col-md-12">
                                <input value="{{ $index }}" name="meta[hero_items][itration][]" type="hidden" required>
                            </div>
                            <div class="col-md-6">
                                {{-- <label class="form-label">Image <span class="text-danger">*</span></label> --}}
                                <div class="form-group mb-2">
                                    <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                        </div>
                                        <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                        <input type="hidden" name="meta[hero_items][image][]" class="selected-files" value="{{ $hero_items['image'][$index] ?? '' }}" required>
                                    </div>
                                    <div class="file-preview box sm"></div>
                                </div>
                            </div>
                            <div class="col-md-6 form-group mb-2">
                                {{-- <label class="form-label">Description <span class="text-danger">*</span></label> --}}
                                <textarea name="meta[hero_items][description][]" class="form-control" rows="4" required>{{ $hero_items['description'][$index] ?? '' }}</textarea>
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
                            <input value="data" name="meta[hero_items][itration][]" type="hidden" required>
                        </div>
                        <div class="col-md-6">
                            {{-- <label class="form-label">Image <span class="text-danger">*</span></label> --}}
                            <div class="form-group mb-2">
                                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                    <input type="hidden" name="meta[hero_items][image][]" class="selected-files" required>
                                </div>
                                <div class="file-preview box sm"></div>
                            </div>
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            {{-- <label class="form-label">Description <span class="text-danger">*</span></label> --}}
                            <textarea name="meta[hero_items][description][]" class="form-control" rows="4" placeholder="Enter description *" required></textarea>
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
        data-target=".hero-items-target"
    >
        <i class="ti ti-plus"></i>
        <span class="ml-2">Add More</span>
    </button>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Sustainable Packaging Vision Section</h4>
    </div>
    <div class="col-md-6">
        <label class="form-label">Image <span class="text-danger">*</span></label>
        <div class="form-group mb-2">
            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                </div>
                <div class="form-control file-amount">{{ __('Choose File') }}</div>
                <input value="{{ $sustainable_packaging_vision_image }}" type="hidden" name="meta[sustainable_packaging_vision_image]" class="selected-files" required>
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div>
    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[sustainable_packaging_vision_title]" value="{{ $sustainable_packaging_vision_title }}" required>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Why Carton Packaging Matters Section</h4>
    </div>
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[why_carton_title]" value="{{ $why_carton_title }}" required>
    </div>
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Description <span class="text-danger">*</span></label>
        <textarea name="meta[why_carton_description]" class="form-control text-editor" rows="4" required>{{ $why_carton_description }}</textarea>
    </div>

    <div class="why-carton-items-target col-md-12">
        @if(isset($why_carton_items['itration']) && is_array($why_carton_items['itration']))
            @foreach($why_carton_items['itration'] as $index => $itration)
                <div class="row remove-parent">
                    <div class="col-md-11">
                        <div class="row">
                            <div class="col-md-12">
                                <input value="{{ $index }}" name="meta[why_carton_items][itration][]" type="hidden" required>
                            </div>
                            <div class="col-md-4">
                                {{-- <label class="form-label">Image <span class="text-danger">*</span></label> --}}
                                <div class="form-group mb-2">
                                    <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                        </div>
                                        <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                        <input type="hidden" name="meta[why_carton_items][image][]" class="selected-files" value="{{ $why_carton_items['image'][$index] ?? '' }}" required>
                                    </div>
                                    <div class="file-preview box sm"></div>
                                </div>
                            </div>
                            <div class="col-md-4 form-group mb-2">
                                {{-- <label class="form-label">Title <span class="text-danger">*</span></label> --}}
                                <input type="text" name="meta[why_carton_items][title][]" class="form-control" value="{{ $why_carton_items['title'][$index] ?? '' }}" required>
                            </div>
                            <div class="col-md-4 form-group mb-2">
                                {{-- <label class="form-label">Description <span class="text-danger">*</span></label> --}}
                                <textarea name="meta[why_carton_items][description][]" class="form-control" rows="3" required>{{ $why_carton_items['description'][$index] ?? '' }}</textarea>
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
                            <input value="data" name="meta[why_carton_items][itration][]" type="hidden" required>
                        </div>
                        <div class="col-md-4">
                            {{-- <label class="form-label">Image <span class="text-danger">*</span></label> --}}
                            <div class="form-group mb-2">
                                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                    <input type="hidden" name="meta[why_carton_items][image][]" class="selected-files" required>
                                </div>
                                <div class="file-preview box sm"></div>
                            </div>
                        </div>
                        <div class="col-md-4 form-group mb-2">
                            {{-- <label class="form-label">Title <span class="text-danger">*</span></label> --}}
                            <input type="text" name="meta[why_carton_items][title][]" class="form-control" placeholder="Enter title *" required>
                        </div>
                        <div class="col-md-4 form-group mb-2">
                            {{-- <label class="form-label">Description <span class="text-danger">*</span></label> --}}
                            <textarea name="meta[why_carton_items][description][]" class="form-control" rows="3" placeholder="Enter description *" required></textarea>
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
        data-target=".why-carton-items-target"
    >
        <i class="ti ti-plus"></i>
        <span class="ml-2">Add More</span>
    </button>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Lamipak Sustainability Commitment Section</h4>
    </div>
    <div class="col-md-6">
        <label class="form-label">Image <span class="text-danger">*</span></label>
        <div class="form-group mb-2">
            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                </div>
                <div class="form-control file-amount">{{ __('Choose File') }}</div>
                <input value="{{ $lamipak_commitment_image }}" type="hidden" name="meta[lamipak_commitment_image]" class="selected-files" required>
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div>
    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <textarea name="meta[lamipak_commitment_title]" class="form-control" rows="4" required>{{ $lamipak_commitment_title }}</textarea>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Carton Packaging Impact & Statistics Section</h4>
    </div>
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[impact_statistics_title]" value="{{ $impact_statistics_title }}" required>
    </div>
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Description <span class="text-danger">*</span></label>
        <textarea name="meta[impact_statistics_description]" class="form-control text-editor" rows="4" required>{{ $impact_statistics_description }}</textarea>
    </div>

    <div class="impact-statistics-items-target col-md-12">
        @if(isset($impact_statistics_items['itration']) && is_array($impact_statistics_items['itration']))
            @foreach($impact_statistics_items['itration'] as $index => $itration)
                <div class="row remove-parent">
                    <div class="col-md-11">
                        <div class="row">
                            <div class="col-md-12">
                                <input value="{{ $index }}" name="meta[impact_statistics_items][itration][]" type="hidden" required>
                            </div>
                            <div class="col-md-6 form-group mb-2">
                                {{-- <label class="form-label">Title <span class="text-danger">*</span></label> --}}
                                <input type="text" name="meta[impact_statistics_items][title][]" class="form-control" value="{{ $impact_statistics_items['title'][$index] ?? '' }}" required>
                            </div>
                            <div class="col-md-6 form-group mb-2">
                                {{-- <label class="form-label">Description <span class="text-danger">*</span></label> --}}
                                <textarea name="meta[impact_statistics_items][description][]" class="form-control" rows="3" required>{{ $impact_statistics_items['description'][$index] ?? '' }}</textarea>
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
        data-limit="15"
        data-content='
            <div class="row remove-parent">
                <div class="col-md-11">
                    <div class="row">
                        <div class="col-md-12">
                            <input value="data" name="meta[impact_statistics_items][itration][]" type="hidden" required>
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            {{-- <label class="form-label">Title <span class="text-danger">*</span></label> --}}
                            <input type="text" name="meta[impact_statistics_items][title][]" class="form-control" placeholder="Enter title *" required>
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            {{-- <label class="form-label">Description <span class="text-danger">*</span></label> --}}
                            <textarea name="meta[impact_statistics_items][description][]" class="form-control" rows="3" placeholder="Enter description *" required></textarea>
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
        data-target=".impact-statistics-items-target"
    >
        <i class="ti ti-plus"></i>
        <span class="ml-2">Add More</span>
    </button>

    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Description <span class="text-danger">*</span></label>
        <textarea name="meta[impact_statistics_footer_description]" class="form-control" rows="4" required>{{ $impact_statistics_footer_description }}</textarea>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Recycling. Journey Visual Section</h4>
    </div>
    <div class="col-md-12">
        <label class="form-label">Image <span class="text-danger">*</span></label>
        <div class="form-group mb-2">
            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                </div>
                <div class="form-control file-amount">{{ __('Choose File') }}</div>
                <input value="{{ $recycling_journey_image }}" type="hidden" name="meta[recycling_journey_image]" class="selected-files" required>
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div>
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Description <span class="text-danger">*</span></label>
        <textarea name="meta[recycling_journey_description]" class="form-control text-editor" rows="4" required>{{ $recycling_journey_description }}</textarea>
    </div>
</div>
