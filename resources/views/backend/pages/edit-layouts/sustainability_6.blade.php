@php
    $breadcrumb_image = $pageData->meta->where('meta_key', 'breadcrumb_image')->first()->meta_value ?? '';

    $short_summary_image = $pageData->meta->where('meta_key', 'short_summary_image')->first()->meta_value ?? '';
    $short_summary_description = $pageData->meta->where('meta_key', 'short_summary_description')->first()->meta_value ?? '';

    $path_title = $pageData->meta->where('meta_key', 'path_title')->first()->meta_value ?? '';
    $path_items = json_decode($pageData->meta->where('meta_key', 'path_items')->first()->meta_value ?? '[]', true);
    $path_items = is_array($path_items) ? $path_items : [];

    $Net_Zero_Target_text = $pageData->meta->where('meta_key', 'Net_Zero_Target_text')->first()->meta_value ?? '';
    $Net_Zero_Target_url = $pageData->meta->where('meta_key', 'Net_Zero_Target_url')->first()->meta_value ?? '';

    $sustainability_section_title = $pageData->meta->where('meta_key', 'sustainability_section_title')->first()->meta_value ?? '';
    $sustainability_section_items = json_decode($pageData->meta->where('meta_key', 'sustainability_section_items')->first()->meta_value ?? '[]', true);
    $sustainability_section_items = is_array($sustainability_section_items) ? $sustainability_section_items : [];
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
        <h4 class="text-primary">Path Section</h4>
    </div>
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[path_title]" value="{{ $path_title }}" required>
    </div>

    <div class="path-items-target col-md-12">
        @if(isset($path_items['itration']) && is_array($path_items['itration']))
            @foreach($path_items['itration'] as $index => $itration)
                <div class="row remove-parent">
                    <div class="col-md-11">
                        <div class="row">
                            <div class="col-md-12">
                                <input value="{{ $index }}" name="meta[path_items][itration][]" type="hidden" required>
                            </div>
                            <div class="col-md-4 form-group mb-2">
                                {{-- <label class="form-label">Icon <span class="text-danger">*</span></label> --}}
                                <div class="form-group mb-2">
                                    <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                        </div>
                                        <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                        <input type="hidden" name="meta[path_items][icon][]" class="selected-files" value="{{ $path_items['icon'][$index] ?? '' }}" required>
                                    </div>
                                    <div class="file-preview box sm"></div>
                                </div>
                            </div>
                            <div class="col-md-4 form-group mb-2">
                                {{-- <label class="form-label">Title <span class="text-danger">*</span></label> --}}
                                <input type="text" name="meta[path_items][title][]" class="form-control" value="{{ $path_items['title'][$index] ?? '' }}" required>
                            </div>
                            <div class="col-md-4 form-group mb-2">
                                {{-- <label class="form-label">Year <span class="text-danger">*</span></label> --}}
                                <input type="text" name="meta[path_items][year][]" class="form-control" value="{{ $path_items['year'][$index] ?? '' }}" required>
                            </div>
                            <div class="col-md-12 form-group mb-2">
                                {{-- <label class="form-label">Description <span class="text-danger">*</span></label> --}}
                                <textarea name="meta[path_items][description][]" class="form-control text-editor" rows="3" required>{{ $path_items['description'][$index] ?? '' }}</textarea>
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
                            <input value="data" name="meta[path_items][itration][]" type="hidden" required>
                        </div>
                        <div class="col-md-4 form-group mb-2">
                            {{-- <label class="form-label">Icon <span class="text-danger">*</span></label> --}}
                            <div class="form-group mb-2">
                                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                    <input type="hidden" name="meta[path_items][icon][]" class="selected-files" required>
                                </div>
                                <div class="file-preview box sm"></div>
                            </div>
                        </div>
                        <div class="col-md-4 form-group mb-2">
                            {{-- <label class="form-label">Title <span class="text-danger">*</span></label> --}}
                            <input type="text" name="meta[path_items][title][]" class="form-control" placeholder="Enter title *" required>
                        </div>
                        <div class="col-md-4 form-group mb-2">
                            {{-- <label class="form-label">Year <span class="text-danger">*</span></label> --}}
                            <input type="text" name="meta[path_items][year][]" class="form-control" placeholder="Enter year *" required>
                        </div>
                        <div class="col-md-12 form-group mb-2">
                            {{-- <label class="form-label">Description <span class="text-danger">*</span></label> --}}
                            <textarea name="meta[path_items][description][]" class="form-control text-editor" rows="3" placeholder="Enter description *" required></textarea>
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
        data-target=".path-items-target"
    >
        <i class="ti ti-plus"></i>
        <span class="ml-2">Add More</span>
    </button>


    <div class="col-md-6 form-group mb-2 mt-2">
        <label class="form-label">Net Zero Target (Value Chain) Text <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[Net_Zero_Target_text]" value="{{ $Net_Zero_Target_text }}" required>
    </div>

    <div class="col-md-6 form-group mb-2 mt-2">
        <label class="form-label">Net Zero Target (Value Chain) Url <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[Net_Zero_Target_url]" value="{{ $Net_Zero_Target_url }}" required>
    </div>

</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Sustainability Section</h4>
    </div>
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[sustainability_section_title]" value="{{ $sustainability_section_title }}" required>
    </div>

    <div class="sustainability-section-items-target col-md-12">
        @if(isset($sustainability_section_items['itration']) && is_array($sustainability_section_items['itration']))
            @foreach($sustainability_section_items['itration'] as $index => $itration)
                <div class="row remove-parent">
                    <div class="col-md-11">
                        <div class="row">
                            <div class="col-md-12">
                                <input value="{{ $index }}" name="meta[sustainability_section_items][itration][]" type="hidden" required>
                            </div>
                            <div class="col-md-6 form-group mb-2">
                                {{-- <label class="form-label">Icon <span class="text-danger">*</span></label> --}}
                                <div class="form-group mb-2">
                                    <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                        </div>
                                        <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                        <input type="hidden" name="meta[sustainability_section_items][icon][]" class="selected-files" value="{{ $sustainability_section_items['icon'][$index] ?? '' }}" required>
                                    </div>
                                    <div class="file-preview box sm"></div>
                                </div>
                            </div>
                            <div class="col-md-6 form-group mb-2">
                                {{-- <label class="form-label">Title <span class="text-danger">*</span></label> --}}
                                <input type="text" name="meta[sustainability_section_items][title][]" class="form-control" value="{{ $sustainability_section_items['title'][$index] ?? '' }}" required>
                            </div>
                            <div class="col-md-12 form-group mb-2">
                                {{-- <label class="form-label">Description <span class="text-danger">*</span></label> --}}
                                <textarea name="meta[sustainability_section_items][description][]" class="form-control" rows="3" required>{{ $sustainability_section_items['description'][$index] ?? '' }}</textarea>
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
                            <input value="data" name="meta[sustainability_section_items][itration][]" type="hidden" required>
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            {{-- <label class="form-label">Icon <span class="text-danger">*</span></label> --}}
                            <div class="form-group mb-2">
                                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                    <input type="hidden" name="meta[sustainability_section_items][icon][]" class="selected-files" required>
                                </div>
                                <div class="file-preview box sm"></div>
                            </div>
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            {{-- <label class="form-label">Title <span class="text-danger">*</span></label> --}}
                            <input type="text" name="meta[sustainability_section_items][title][]" class="form-control" placeholder="Enter title" required>
                        </div>
                        <div class="col-md-12 form-group mb-2">
                            {{-- <label class="form-label">Description <span class="text-danger">*</span></label> --}}
                            <textarea name="meta[sustainability_section_items][description][]" class="form-control" rows="3" placeholder="Enter description" required></textarea>
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
        data-target=".sustainability-section-items-target"
    >
        <i class="ti ti-plus"></i>
        <span class="ml-2">Add More</span>
    </button>
</div>
