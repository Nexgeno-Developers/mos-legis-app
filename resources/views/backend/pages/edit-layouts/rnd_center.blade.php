@php
    // Breadcrumb Section
    $breadcrumb_image = $pageData->meta->where('meta_key', 'breadcrumb_image')->first()->meta_value ?? '';
    $breadcrumb_title = $pageData->meta->where('meta_key', 'breadcrumb_title')->first()->meta_value ?? '';
    $breadcrumb_description = $pageData->meta->where('meta_key', 'breadcrumb_description')->first()->meta_value ?? '';

    // Short Summery Section
    $short_summary_title = $pageData->meta->where('meta_key', 'short_summary_title')->first()->meta_value ?? '';
    $short_summary_image = $pageData->meta->where('meta_key', 'short_summary_image')->first()->meta_value ?? '';
    $short_summary_description = $pageData->meta->where('meta_key', 'short_summary_description')->first()->meta_value ?? '';

    // Hero Section
    $hero_title = $pageData->meta->where('meta_key', 'hero_title')->first()->meta_value ?? '';
    $hero_description = $pageData->meta->where('meta_key', 'hero_description')->first()->meta_value ?? '';
    $hero_items = json_decode($pageData->meta->where('meta_key', 'hero_items')->first()->meta_value ?? '[]', true);
    $hero_items = is_array($hero_items) ? $hero_items : [];
    $hero_explore_capabilities = $pageData->meta->where('meta_key', 'hero_explore_capabilities')->first()->meta_value ?? '';
    $hero_talk_to_team = $pageData->meta->where('meta_key', 'hero_talk_to_team')->first()->meta_value ?? '';

    // Lifecycle Section
    $lifecycle_title = $pageData->meta->where('meta_key', 'lifecycle_title')->first()->meta_value ?? '';
    $lifecycle_items = json_decode($pageData->meta->where('meta_key', 'lifecycle_items')->first()->meta_value ?? '[]', true);
    $lifecycle_items = is_array($lifecycle_items) ? $lifecycle_items : [];

    // Laboratory Zones Section
    $laboratory_zones_title = $pageData->meta->where('meta_key', 'laboratory_zones_title')->first()->meta_value ?? '';
    $laboratory_zones_subtitle = $pageData->meta->where('meta_key', 'laboratory_zones_subtitle')->first()->meta_value ?? '';
    $laboratory_zones_items = json_decode($pageData->meta->where('meta_key', 'laboratory_zones_items')->first()->meta_value ?? '[]', true);
    $laboratory_zones_items = is_array($laboratory_zones_items) ? $laboratory_zones_items : [];

    // Consultation Section
    $consultation_background_image = $pageData->meta->where('meta_key', 'consultation_background_image')->first()->meta_value ?? '';
    $consultation_title = $pageData->meta->where('meta_key', 'consultation_title')->first()->meta_value ?? '';
    $consultation_description = $pageData->meta->where('meta_key', 'consultation_description')->first()->meta_value ?? '';
    $consultation_cta_title = $pageData->meta->where('meta_key', 'consultation_cta_title')->first()->meta_value ?? '';
    $consultation_cta_url = $pageData->meta->where('meta_key', 'consultation_cta_url')->first()->meta_value ?? '';

@endphp

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Breadcrumb Section</h4>
    </div>
    <div class="col-md-6 form-group mb-2">
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
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Description <span class="text-danger">*</span></label>
        <textarea name="meta[breadcrumb_description]" class="form-control" rows="4" required>{{ $breadcrumb_description }}</textarea>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Short Summery Section</h4>
    </div>
    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[short_summary_title]" value="{{ $short_summary_title }}" required>
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
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[hero_title]" value="{{ $hero_title }}" required>
    </div>
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Description <span class="text-danger">*</span></label>
        <textarea name="meta[hero_description]" class="form-control text-editor" rows="4" required>{{ $hero_description }}</textarea>
    </div>

    <div class="rnd-hero-items-target col-md-12">
        @if(isset($hero_items['itration']) && is_array($hero_items['itration']))
            @foreach($hero_items['itration'] as $index => $itration)
                <div class="row remove-parent">
                    <div class="col-md-11">
                        <div class="row">
                            <div class="col-md-12">
                                <input value="{{ $index }}" name="meta[hero_items][itration][]" type="hidden" required>
                            </div>
                            <div class="col-md-4 form-group mb-2">
                                <label class="form-label">Icon <span class="text-danger">*</span></label>
                                <div class="form-group mb-2">
                                    <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                        </div>
                                        <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                        <input type="hidden" name="meta[hero_items][icon][]" class="selected-files" value="{{ $hero_items['icon'][$index] ?? '' }}" required>
                                    </div>
                                    <div class="file-preview box sm"></div>
                                </div>
                            </div>
                            <div class="col-md-8 form-group mb-2">
                                <label class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" name="meta[hero_items][title][]" class="form-control" value="{{ $hero_items['title'][$index] ?? '' }}" required>
                            </div>
                            <div class="col-md-12 form-group mb-2">
                                <label class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea name="meta[hero_items][description][]" class="form-control" rows="3" required>{{ $hero_items['description'][$index] ?? '' }}</textarea>
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
        data-limit="20"
        data-content='
            <div class="row remove-parent">
                <div class="col-md-11">
                    <div class="row">
                        <div class="col-md-12">
                            <input value="data" name="meta[hero_items][itration][]" type="hidden" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Icon <span class="text-danger">*</span></label>
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
                        <div class="col-md-8 form-group mb-2">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="meta[hero_items][title][]" class="form-control" placeholder="Enter title" required>
                        </div>
                        <div class="col-md-12 form-group mb-2">
                            <label class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea name="meta[hero_items][description][]" class="form-control" rows="3" placeholder="Enter description" required></textarea>
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
        data-target=".rnd-hero-items-target"
    >
        <i class="ti ti-plus"></i>
        <span class="ml-2">Add More</span>
    </button>

    <div class="col-md-6 form-group mb-2 mt-2">
        <label class="form-label">Explore our Capabilities <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[hero_explore_capabilities]" value="{{ $hero_explore_capabilities }}" required>
    </div>
    <div class="col-md-6 form-group mb-2 mt-2 d-none">
        <label class="form-label">Talk to Our Team <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[hero_talk_to_team]" value="{{ $hero_talk_to_team }}">
    </div>
</div>

<div class="row d-none">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Lifecycle Section</h4>
    </div>
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[lifecycle_title]" value="{{ $lifecycle_title }}">
    </div>

    <div class="lifecycle-items-target col-md-12">
        @if(isset($lifecycle_items['itration']) && is_array($lifecycle_items['itration']))
            @foreach($lifecycle_items['itration'] as $index => $itration)
                <div class="row remove-parent">
                    <div class="col-md-11">
                        <div class="row">
                            <div class="col-md-12">
                                <input value="{{ $index }}" name="meta[lifecycle_items][itration][]" type="hidden" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Icon <span class="text-danger">*</span></label>
                                <div class="form-group mb-2">
                                    <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                        </div>
                                        <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                        <input type="hidden" name="meta[lifecycle_items][icon][]" class="selected-files" value="{{ $lifecycle_items['icon'][$index] ?? '' }}">
                                    </div>
                                    <div class="file-preview box sm"></div>
                                </div>
                            </div>
                            <div class="col-md-8 form-group mb-2">
                                <label class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" name="meta[lifecycle_items][title][]" class="form-control" value="{{ $lifecycle_items['title'][$index] ?? '' }}">
                            </div>
                            <div class="col-md-12 form-group mb-2">
                                <label class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea name="meta[lifecycle_items][description][]" class="form-control" rows="3">{{ $lifecycle_items['description'][$index] ?? '' }}</textarea>
                            </div>
                            <div class="col-md-12 form-group mb-2">
                                <label class="form-label">Key Points</label>
                                <input type="text" class="form-control aiz-tag-input" name="meta[lifecycle_items][key_points][]" value="{{ $lifecycle_items['key_points'][$index] ?? '' }}" placeholder="Enter tags separated by commas">
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
        data-limit="20"
        data-content='
            <div class="row remove-parent">
                <div class="col-md-11">
                    <div class="row">
                        <div class="col-md-12">
                            <input value="data" name="meta[lifecycle_items][itration][]" type="hidden" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Icon <span class="text-danger">*</span></label>
                            <div class="form-group mb-2">
                                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                    <input type="hidden" name="meta[lifecycle_items][icon][]" class="selected-files" required>
                                </div>
                                <div class="file-preview box sm"></div>
                            </div>
                        </div>
                        <div class="col-md-8 form-group mb-2">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="meta[lifecycle_items][title][]" class="form-control" placeholder="Enter title" required>
                        </div>
                        <div class="col-md-12 form-group mb-2">
                            <label class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea name="meta[lifecycle_items][description][]" class="form-control" rows="3" placeholder="Enter description" required></textarea>
                        </div>
                        <div class="col-md-12 form-group mb-2">
                            <label class="form-label">Key Points</label>
                            <input type="text" class="form-control aiz-tag-input" name="meta[lifecycle_items][key_points][]" value="" placeholder="Enter tags separated by commas">
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
        data-target=".lifecycle-items-target"
    >
        <i class="ti ti-plus"></i>
        <span class="ml-2">Add More</span>
    </button>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Laboratory Zones Section</h4>
    </div>
    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[laboratory_zones_title]" value="{{ $laboratory_zones_title }}" required>
    </div>

    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Subtitle <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[laboratory_zones_subtitle]" value="{{ $laboratory_zones_subtitle }}" required>
    </div>

    <div class="laboratory-zones-items-target col-md-12">
        @if(isset($laboratory_zones_items['itration']) && is_array($laboratory_zones_items['itration']))
            @foreach($laboratory_zones_items['itration'] as $index => $itration)
                <div class="row remove-parent">
                    <div class="col-md-11">
                        <div class="row">
                            <div class="col-md-12">
                                <input value="{{ $index }}" name="meta[laboratory_zones_items][itration][]" type="hidden" required>
                            </div>
                            <div class="col-md-12 form-group mb-2">
                                <label class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" name="meta[laboratory_zones_items][title][]" class="form-control" value="{{ $laboratory_zones_items['title'][$index] ?? '' }}" required>
                            </div>
                            <div class="col-md-12 form-group mb-2">
                                <label class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea name="meta[laboratory_zones_items][description][]" class="form-control" rows="3" required>{{ $laboratory_zones_items['description'][$index] ?? '' }}</textarea>
                            </div>
                            <div class="col-md-12 form-group mb-2">
                                <label class="form-label">Key Points</label>
                                <input type="text" class="form-control aiz-tag-input" name="meta[laboratory_zones_items][key_points][]" value="{{ $laboratory_zones_items['key_points'][$index] ?? '' }}" placeholder="Enter tags separated by commas">
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
        data-limit="20"
        data-content='
            <div class="row remove-parent">
                <div class="col-md-11">
                    <div class="row">
                        <div class="col-md-12">
                            <input value="data" name="meta[laboratory_zones_items][itration][]" type="hidden" required>
                        </div>
                        <div class="col-md-12 form-group mb-2">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="meta[laboratory_zones_items][title][]" class="form-control" placeholder="Enter title" required>
                        </div>
                        <div class="col-md-12 form-group mb-2">
                            <label class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea name="meta[laboratory_zones_items][description][]" class="form-control" rows="3" placeholder="Enter description" required></textarea>
                        </div>
                        <div class="col-md-12 form-group mb-2">
                            <label class="form-label">Key Points</label>
                            <input type="text" class="form-control aiz-tag-input" name="meta[laboratory_zones_items][key_points][]" value="" placeholder="Enter tags separated by commas">
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
        data-target=".laboratory-zones-items-target"
    >
        <i class="ti ti-plus"></i>
        <span class="ml-2">Add More</span>
    </button>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Consultation Section</h4>
    </div>
    <div class="col-md-6 form-group mb-2 d-none">
        <label class="form-label">Background Image <span class="text-danger">*</span></label>
        <div class="form-group mb-2">
            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                </div>
                <div class="form-control file-amount">{{ __('Choose File') }}</div>
                <input value="{{ $consultation_background_image }}" type="hidden" name="meta[consultation_background_image]" class="selected-files">
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div>
    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[consultation_title]" value="{{ $consultation_title }}" required>
    </div>

    <div class="col-md-6 form-group mb-2">
        <label class="form-label">CTA URL <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[consultation_cta_url]" value="{{ $consultation_cta_url }}" required>
    </div>

    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Description <span class="text-danger">*</span></label>
        <textarea name="meta[consultation_description]" class="form-control" rows="4" required>{{ $consultation_description }}</textarea>
    </div>
    
    <div class="col-md-6 form-group mb-2 d-none">
        <label class="form-label">CTA Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[consultation_cta_title]" value="{{ $consultation_cta_title }}">
    </div>

</div>

