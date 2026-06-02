@php
    $breadcrumb_image = $pageData->meta->where('meta_key', 'breadcrumb_image')->first()->meta_value ?? '';

    $short_summary_image = $pageData->meta->where('meta_key', 'short_summary_image')->first()->meta_value ?? '';
    $short_summary_description = $pageData->meta->where('meta_key', 'short_summary_description')->first()->meta_value ?? '';

    $hero_title = $pageData->meta->where('meta_key', 'hero_title')->first()->meta_value ?? '';
    $hero_description_intro = $pageData->meta->where('meta_key', 'hero_description_intro')->first()->meta_value ?? '';
    $hero_items = json_decode($pageData->meta->where('meta_key', 'hero_items')->first()->meta_value ?? '[]', true);
    $hero_items = is_array($hero_items) ? $hero_items : [];
    $hero_description_footer = $pageData->meta->where('meta_key', 'hero_description_footer')->first()->meta_value ?? '';

    $project_items = json_decode($pageData->meta->where('meta_key', 'project_items')->first()->meta_value ?? '[]', true);
    $project_items = is_array($project_items) ? $project_items : [];

    $sustainability_journey_title = $pageData->meta->where('meta_key', 'sustainability_journey_title')->first()->meta_value ?? '';
    $sustainability_journey_image = $pageData->meta->where('meta_key', 'sustainability_journey_image')->first()->meta_value ?? '';
    $sustainability_journey_description = $pageData->meta->where('meta_key', 'sustainability_journey_description')->first()->meta_value ?? '';
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
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Description <span class="text-danger">*</span></label>
        <textarea name="meta[hero_description_intro]" class="form-control" rows="4" required>{{ $hero_description_intro }}</textarea>
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
                                {{-- <label class="form-label">Title <span class="text-danger">*</span></label> --}}
                                <input type="text" name="meta[hero_items][title][]" class="form-control" value="{{ $hero_items['title'][$index] ?? '' }}" required>
                            </div>
                            <div class="col-md-12 form-group mb-2">
                                {{-- <label class="form-label">Description <span class="text-danger">*</span></label> --}}
                                <textarea name="meta[hero_items][description][]" class="form-control text-editor" rows="3" required>{{ $hero_items['description'][$index] ?? '' }}</textarea>
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
                            {{-- <label class="form-label">Title <span class="text-danger">*</span></label> --}}
                            <input type="text" name="meta[hero_items][title][]" class="form-control" placeholder="Enter title *" required>
                        </div>
                        <div class="col-md-12 form-group mb-2">
                            {{-- <label class="form-label">Description <span class="text-danger">*</span></label> --}}
                            <textarea name="meta[hero_items][description][]" class="form-control text-editor" rows="3" placeholder="Enter description *" required></textarea>
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

    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Description <span class="text-danger">*</span></label>
        <textarea name="meta[hero_description_footer]" class="form-control" rows="4" required>{{ $hero_description_footer }}</textarea>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Project Section</h4>
    </div>

    <div class="project-items-target col-md-12">
        @if(isset($project_items['itration']) && is_array($project_items['itration']))
            @foreach($project_items['itration'] as $index => $itration)
                <div class="row remove-parent">
                    <div class="col-md-11">
                        <div class="row">
                            <div class="col-md-12">
                                <input value="{{ $index }}" name="meta[project_items][itration][]" type="hidden" required>
                            </div>
                            <div class="col-md-12 form-group mb-2">
                                {{-- <label class="form-label">Content <span class="text-danger">*</span></label> --}}
                                <textarea name="meta[project_items][content][]" class="form-control text-editor" rows="4" required>{{ $project_items['content'][$index] ?? '' }}</textarea>
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
                            <input value="data" name="meta[project_items][itration][]" type="hidden" required>
                        </div>
                        <div class="col-md-12 form-group mb-2">
                            {{-- <label class="form-label">Content <span class="text-danger">*</span></label> --}}
                            <textarea name="meta[project_items][content][]" class="form-control text-editor" rows="4" placeholder="Enter content *" required></textarea>
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
        data-target=".project-items-target"
    >
        <i class="ti ti-plus"></i>
        <span class="ml-2">Add More</span>
    </button>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Sustainability Journey Section</h4>
    </div>
    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[sustainability_journey_title]" value="{{ $sustainability_journey_title }}" required>
    </div>
    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Image <span class="text-danger">*</span></label>
        <div class="form-group mb-2">
            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                </div>
                <div class="form-control file-amount">{{ __('Choose File') }}</div>
                <input value="{{ $sustainability_journey_image }}" type="hidden" name="meta[sustainability_journey_image]" class="selected-files" required>
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div>
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Description <span class="text-danger">*</span></label>
        <textarea name="meta[sustainability_journey_description]" class="form-control text-editor" rows="4" required>{{ $sustainability_journey_description }}</textarea>
    </div>
</div>
