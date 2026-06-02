@php
    $breadcrumb_image = $pageData->meta->where('meta_key', 'breadcrumb_image')->first()->meta_value ?? '';
    $breadcrumb_description = $pageData->meta->where('meta_key', 'breadcrumb_description')->first()->meta_value ?? '';

    $short_summary_image = $pageData->meta->where('meta_key', 'short_summary_image')->first()->meta_value ?? '';
    $short_summary_description = $pageData->meta->where('meta_key', 'short_summary_description')->first()->meta_value ?? '';

    $hero_title = $pageData->meta->where('meta_key', 'hero_title')->first()->meta_value ?? '';
    $hero_subtitle = $pageData->meta->where('meta_key', 'hero_subtitle')->first()->meta_value ?? '';
    $hero_items = json_decode($pageData->meta->where('meta_key', 'hero_items')->first()->meta_value ?? '[]', true);
    $hero_items = is_array($hero_items) ? $hero_items : [];

    $timeline_title = $pageData->meta->where('meta_key', 'timeline_title')->first()->meta_value ?? '';
    $timeline_description = $pageData->meta->where('meta_key', 'timeline_description')->first()->meta_value ?? '';
    $timeline_items = json_decode($pageData->meta->where('meta_key', 'timeline_items')->first()->meta_value ?? '[]', true);
    $timeline_items = is_array($timeline_items) ? $timeline_items : [];
    $timeline_certificates = $pageData->meta->where('meta_key', 'timeline_certificates')->first()->meta_value ?? '';
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
        <label class="form-label">Description <span class="text-danger">*</span></label>
        <textarea name="meta[breadcrumb_description]" class="form-control" rows="4" required>{{ $breadcrumb_description }}</textarea>
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
    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[hero_title]" value="{{ $hero_title }}" required>
    </div>
    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Subbitle <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[hero_subtitle]" value="{{ $hero_subtitle }}" required>
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
                                <label class="form-label">Image <span class="text-danger">*</span></label>
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
                                <label class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" name="meta[hero_items][title][]" class="form-control" value="{{ $hero_items['title'][$index] ?? '' }}" required>
                            </div>
                            <div class="col-md-6 form-group mb-2">
                                <label class="form-label">Location <span class="text-danger">*</span></label>
                                <input type="text" name="meta[hero_items][location][]" class="form-control aiz-tag-input" value="{{ $hero_items['location'][$index] ?? '' }}" required>
                            </div>
                            <div class="col-md-6 form-group mb-2">
                                <label class="form-label">Year <span class="text-danger">*</span></label>
                                <input type="text" name="meta[hero_items][year][]" class="form-control" value="{{ $hero_items['year'][$index] ?? '' }}" required>
                            </div>
                            <div class="col-md-12 form-group mb-2">
                                <label class="form-label">Key Points <span class="text-danger">*</span></label>
                                <input type="text" class="form-control aiz-tag-input" name="meta[hero_items][key_points][]" value="{{ $hero_items['key_points'][$index] ?? '' }}" placeholder="Enter tags separated by commas" required>
                            </div>
                            <div class="col-md-12 form-group mb-2">
                                <label class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea name="meta[hero_items][description][]" class="form-control" rows="3" required>{{ $hero_items['description'][$index] ?? '' }}</textarea>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Certificates <span class="text-danger">*</span></label>
                                <div class="form-group mb-2">
                                    <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="true">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                        </div>
                                        <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                        <input type="hidden" name="meta[hero_items][certificates][]" class="selected-files" value="{{ $hero_items['certificates'][$index] ?? '' }}" required>
                                    </div>
                                    <div class="file-preview box sm"></div>
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
        data-limit="15"
        data-content='
            <div class="row remove-parent">
                <div class="col-md-11">
                    <div class="row">
                        <div class="col-md-12">
                            <input value="data" name="meta[hero_items][itration][]" type="hidden" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Image <span class="text-danger">*</span></label>
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
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="meta[hero_items][title][]" class="form-control" placeholder="Enter title" required>
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <label class="form-label">Location <span class="text-danger">*</span></label>
                            <input type="text" name="meta[hero_items][location][]" class="form-control aiz-tag-input" required>
                        </div>                        
                        <div class="col-md-6 form-group mb-2">
                            <label class="form-label">Year <span class="text-danger">*</span></label>
                            <input type="text" name="meta[hero_items][year][]" class="form-control" placeholder="Enter year" required>
                        </div>
                        <div class="col-md-12 form-group mb-2">
                            <label class="form-label">Key Points <span class="text-danger">*</span></label>
                            <input type="text" class="form-control aiz-tag-input" name="meta[hero_items][key_points][]" value="" placeholder="Enter tags separated by commas" required>
                        </div>
                        <div class="col-md-12 form-group mb-2">
                            <label class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea name="meta[hero_items][description][]" class="form-control" rows="3" placeholder="Enter description" required></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Certificates <span class="text-danger">*</span></label>
                            <div class="form-group mb-2">
                                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="true">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                    <input type="hidden" name="meta[hero_items][certificates][]" class="selected-files" required>
                                </div>
                                <div class="file-preview box sm"></div>
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
        data-target=".hero-items-target"
    >
        <i class="ti ti-plus"></i>
        <span class="ml-2">Add More</span>
    </button>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Timeline Section</h4>
    </div>
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[timeline_title]" value="{{ $timeline_title }}" required>
    </div>
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Description <span class="text-danger">*</span></label>
        <textarea name="meta[timeline_description]" class="form-control" rows="4" required>{{ $timeline_description }}</textarea>
    </div>

    <div class="timeline-items-target col-md-12">
        @if(isset($timeline_items['itration']) && is_array($timeline_items['itration']))
            @foreach($timeline_items['itration'] as $index => $itration)
                <div class="row remove-parent">
                    <div class="col-md-11">
                        <div class="row">
                            <div class="col-md-12">
                                <input value="{{ $index }}" name="meta[timeline_items][itration][]" type="hidden" required>
                            </div>
                            <div class="col-md-3 form-group mb-2">
                                <label class="form-label">Year <span class="text-danger">*</span></label>
                                <input type="text" name="meta[timeline_items][year][]" class="form-control" value="{{ $timeline_items['year'][$index] ?? '' }}" required>
                            </div>
                            <div class="col-md-9 form-group mb-2">
                                <label class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" name="meta[timeline_items][title][]" class="form-control" value="{{ $timeline_items['title'][$index] ?? '' }}" required>
                            </div>
                            <div class="col-md-12 form-group mb-2">
                                <label class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea name="meta[timeline_items][description][]" class="form-control" rows="3" required>{{ $timeline_items['description'][$index] ?? '' }}</textarea>
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
                            <input value="data" name="meta[timeline_items][itration][]" type="hidden" required>
                        </div>
                        <div class="col-md-3 form-group mb-2">
                            <label class="form-label">Year <span class="text-danger">*</span></label>
                            <input type="text" name="meta[timeline_items][year][]" class="form-control" placeholder="Enter year" required>
                        </div>
                        <div class="col-md-9 form-group mb-2">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="meta[timeline_items][title][]" class="form-control" placeholder="Enter title" required>
                        </div>
                        <div class="col-md-12 form-group mb-2">
                            <label class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea name="meta[timeline_items][description][]" class="form-control" rows="3" placeholder="Enter description" required></textarea>
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
        data-target=".timeline-items-target"
    >
        <i class="ti ti-plus"></i>
        <span class="ml-2">Add More</span>
    </button>

    <div class="col-md-12">
        <hr>
    </div>
    <div class="col-md-12">
        <label class="form-label">Certificates <span class="text-danger">*</span></label>
        <div class="form-group mb-2">
            <div class="input-group" data-toggle="aizuploader" data-type="document" data-multiple="true">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                </div>
                <div class="form-control file-amount">{{ __('Choose File') }}</div>
                <input value="{{ $timeline_certificates }}" type="hidden" name="meta[timeline_certificates]" class="selected-files" required>
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div>
</div>
