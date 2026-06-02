@php
    $breadcrumb_image = $pageData->meta->where('meta_key', 'breadcrumb_image')->first()->meta_value ?? '';

    // Hero section
    $hero_title = $pageData->meta->where('meta_key', 'hero_title')->first()->meta_value ?? '';
    $hero_description = $pageData->meta->where('meta_key', 'hero_description')->first()->meta_value ?? '';
    $hero_video_url = $pageData->meta->where('meta_key', 'hero_video_url')->first()->meta_value ?? '';
    $hero_navigation_link = $pageData->meta->where('meta_key', 'hero_navigation_link')->first()->meta_value ?? '';

    // Apply section
    $apply_title = $pageData->meta->where('meta_key', 'apply_title')->first()->meta_value ?? '';
    $apply_linkedin_url = $pageData->meta->where('meta_key', 'apply_linkedin_url')->first()->meta_value ?? '';

    // HR section
    $hr_title = $pageData->meta->where('meta_key', 'hr_title')->first()->meta_value ?? '';
    $hr_description = $pageData->meta->where('meta_key', 'hr_description')->first()->meta_value ?? '';
    // $hr_video_url = $pageData->meta->where('meta_key', 'hr_video_url')->first()->meta_value ?? '';
    $hr_photo = $pageData->meta->where('meta_key', 'hr_photo')->first()->meta_value ?? '';
    $hr_name = $pageData->meta->where('meta_key', 'hr_name')->first()->meta_value ?? '';
    $hr_designation = $pageData->meta->where('meta_key', 'hr_designation')->first()->meta_value ?? '';

    // Values section
    $values_title = $pageData->meta->where('meta_key', 'values_title')->first()->meta_value ?? '';
    $values_subtitle = $pageData->meta->where('meta_key', 'values_subtitle')->first()->meta_value ?? '';
    $values_items = json_decode($pageData->meta->where('meta_key', 'values_items')->first()->meta_value ?? '[]', true);
    $values_items = is_array($values_items) ? $values_items : [];

    // Solution section
    $solution_title = $pageData->meta->where('meta_key', 'solution_title')->first()->meta_value ?? '';
    $solution_subtitle = $pageData->meta->where('meta_key', 'solution_subtitle')->first()->meta_value ?? '';
    // $solution_description = $pageData->meta->where('meta_key', 'solution_description')->first()->meta_value ?? '';
    $solution_items = json_decode($pageData->meta->where('meta_key', 'solution_items')->first()->meta_value ?? '[]', true);
    $solution_items = is_array($solution_items) ? $solution_items : [];
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
    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Video Url <span class="text-danger">*</span></label>
        <input type="url" class="form-control" name="meta[hero_video_url]" value="{{ $hero_video_url }}" required>
    </div>
    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Navigation Link <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[hero_navigation_link]" value="{{ $hero_navigation_link }}" required>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Apply Section</h4>
    </div>
    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[apply_title]" value="{{ $apply_title }}" required>
    </div>
    <div class="col-md-6 form-group mb-2">
        <label class="form-label">LinkedIn Url <span class="text-danger">*</span></label>
        <input type="url" class="form-control" name="meta[apply_linkedin_url]" value="{{ $apply_linkedin_url }}" required>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">HR Section</h4>
    </div>
    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[hr_title]" value="{{ $hr_title }}" required>
    </div>
    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Description <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[hr_description]" value="{{ $hr_description }}" required>
    </div>
    {{-- <div class="col-md-4 form-group mb-2">
        <label class="form-label">Video Url <span class="text-danger">*</span></label>
        <input type="url" class="form-control" name="meta[hr_video_url]" value="{{ $hr_video_url }}" required>
    </div> --}}

    <div class="col-md-4">
        <label class="form-label">Photo <span class="text-danger">*</span></label>
        <div class="form-group mb-2">
            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                </div>
                <div class="form-control file-amount">{{ __('Choose File') }}</div>
                <input value="{{ $hr_photo }}" type="hidden" name="meta[hr_photo]" class="selected-files" required>
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div>

    <div class="col-md-4 form-group mb-2">
        <label class="form-label">Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[hr_name]" value="{{ $hr_name }}" required>
    </div>

    <div class="col-md-4 form-group mb-2">
        <label class="form-label">Designation <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[hr_designation]" value="{{ $hr_designation }}" required>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Values Section</h4>
    </div>
    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[values_title]" value="{{ $values_title }}" required>
    </div>
    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Subtitle <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[values_subtitle]" value="{{ $values_subtitle }}" required>
    </div>

    <div class="values-items-target col-md-12">
        @if(isset($values_items['itration']) && is_array($values_items['itration']))
            @foreach($values_items['itration'] as $index => $itration)
                <div class="row remove-parent">
                    <div class="col-md-11">
                        <div class="row">
                            <div class="col-md-12">
                                <input value="{{ $index }}" name="meta[values_items][itration][]" type="hidden" required>
                            </div>
                            <div class="col-md-6 form-group mb-2">
                                {{-- <label class="form-label">Title <span class="text-danger">*</span></label> --}}
                                <input type="text" name="meta[values_items][title][]" class="form-control" value="{{ $values_items['title'][$index] ?? '' }}" required>
                            </div>
                            <div class="col-md-6">
                                {{-- <label class="form-label">Image <span class="text-danger">*</span></label> --}}
                                <div class="form-group mb-2">
                                    <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                        </div>
                                        <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                        <input type="hidden" name="meta[values_items][image][]" class="selected-files" value="{{ $values_items['image'][$index] ?? '' }}" required>
                                    </div>
                                    <div class="file-preview box sm"></div>
                                </div>
                            </div>
                            <div class="col-md-12 form-group mb-2">
                                {{-- <label class="form-label">Description <span class="text-danger">*</span></label> --}}
                                <textarea name="meta[values_items][description][]" class="form-control" rows="4" required>{{ $values_items['description'][$index] ?? '' }}</textarea>
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
                            <input value="data" name="meta[values_items][itration][]" type="hidden" required>
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            {{-- <label class="form-label">Title <span class="text-danger">*</span></label> --}}
                            <input type="text" name="meta[values_items][title][]" class="form-control" placeholder="Enter title *" required>
                        </div>
                        <div class="col-md-6">
                            {{-- <label class="form-label">Image <span class="text-danger">*</span></label> --}}
                            <div class="form-group mb-2">
                                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                    <input type="hidden" name="meta[values_items][image][]" class="selected-files" required>
                                </div>
                                <div class="file-preview box sm"></div>
                            </div>
                        </div>
                        <div class="col-md-12 form-group mb-2">
                            {{-- <label class="form-label">Description <span class="text-danger">*</span></label> --}}
                            <textarea name="meta[values_items][description][]" class="form-control" rows="4" placeholder="Enter description *" required></textarea>
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
        data-target=".values-items-target"
    >
        <i class="ti ti-plus"></i>
        <span class="ml-2">Add More</span>
    </button>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Solution Section</h4>
    </div>
    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[solution_title]" value="{{ $solution_title }}" required>
    </div>
    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Subtitle <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[solution_subtitle]" value="{{ $solution_subtitle }}" required>
    </div>

    {{-- <div class="col-md-12 form-group mb-2">
        <label class="form-label">Description <span class="text-danger">*</span></label>
        <textarea name="meta[solution_description]" class="form-control" rows="4" required>{{ $solution_description }}</textarea>
    </div> --}}

    <div class="solution-items-target col-md-12">
        @if(isset($solution_items['itration']) && is_array($solution_items['itration']))
            @foreach($solution_items['itration'] as $index => $itration)
                <div class="row remove-parent">
                    <div class="col-md-11">
                        <div class="row">
                            <div class="col-md-12">
                                <input value="{{ $index }}" name="meta[solution_items][itration][]" type="hidden" required>
                            </div>
                            <div class="col-md-12 form-group mb-2">
                                <label class="form-label">Video Url <span class="text-danger">*</span></label>
                                <input type="url" class="form-control" name="meta[solution_items][video_url][]" value="{{ $solution_items['video_url'][$index] ?? '' }}" required>
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
                            <input value="data" name="meta[solution_items][itration][]" type="hidden" required>
                        </div>
                        <div class="col-md-12 form-group mb-2">
                            <label class="form-label">Video Url <span class="text-danger">*</span></label>
                            <input type="url" class="form-control" name="meta[solution_items][video_url][]" placeholder="https://..." required>
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
        data-target=".solution-items-target"
    >
        <i class="ti ti-plus"></i>
        <span class="ml-2">Add More</span>
    </button>
</div>

