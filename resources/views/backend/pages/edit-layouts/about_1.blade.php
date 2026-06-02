@php
    use App\Models\Page;

    $breadcrumb_image = $pageData->meta->where('meta_key', 'breadcrumb_image')->first()->meta_value ?? '';

    // Short Summery Section
    $short_summary_title = $pageData->meta->where('meta_key', 'short_summary_title')->first()->meta_value ?? '';
    $short_summary_image = $pageData->meta->where('meta_key', 'short_summary_image')->first()->meta_value ?? '';
    $short_summary_icon = $pageData->meta->where('meta_key', 'short_summary_icon')->first()->meta_value ?? '';
    $short_summary_description = $pageData->meta->where('meta_key', 'short_summary_description')->first()->meta_value ?? '';

    // Page Block Section
    $selected_page_blocks = json_decode(
        $pageData->meta->where('meta_key', 'page_blocks')->first()->meta_value ?? '[]',
        true
    );
    $selected_page_blocks = is_array($selected_page_blocks) ? $selected_page_blocks : [];

    $pageBlocksQuery = Page::query()->whereNotIn('layout', ['default', 'home', 'example']);
    if (auth()->user()?->company_id) {
        $pageBlocksQuery->where('company_id', auth()->user()->company_id);
    }
    $page_blocks = $pageBlocksQuery->orderBy('title')->get(['id', 'title']);

    $business_statistics_items = json_decode($pageData->meta->where('meta_key', 'business_statistics_items')->first()->meta_value ?? '[]', true);
    $business_statistics_items = is_array($business_statistics_items) ? $business_statistics_items : [];

    $journey_items = json_decode($pageData->meta->where('meta_key', 'journey_items')->first()->meta_value ?? '[]', true);
    $journey_items = is_array($journey_items) ? $journey_items : [];

    $video_url = $pageData->meta->where('meta_key', 'video_url')->first()->meta_value ?? '';
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

    <div class="col-md-4 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[short_summary_title]" value="{{ $short_summary_title }}" required>
    </div>

    <div class="col-md-4">
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

    <div class="col-md-4">
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

    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Short Description <span class="text-danger">*</span></label>
        <textarea name="meta[short_summary_description]" class="form-control" rows="4" required>{{ $short_summary_description }}</textarea>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Page Block Section</h4>
    </div>
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Page <span class="text-danger">*</span></label>
        <select class="form-control select2" name="meta[page_blocks][]" multiple required>
            @foreach($page_blocks as $blockPage)
                <option value="{{ $blockPage->id }}" {{ in_array($blockPage->id, $selected_page_blocks) ? 'selected' : '' }}>
                    {{ $blockPage->title }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Business Statistics Section</h4>
    </div>

    <div class="business-statistics-items-target col-md-12">
        @if(isset($business_statistics_items['itration']) && is_array($business_statistics_items['itration']))
            @foreach($business_statistics_items['itration'] as $index => $itration)
                <div class="row remove-parent">
                    <div class="col-md-11">
                        <div class="row">
                            <div class="col-md-12">
                                <input value="{{ $index }}" name="meta[business_statistics_items][itration][]" type="hidden" required>
                            </div>
                            <div class="col-md-4">
                                {{-- <label class="form-label">Icon image <span class="text-danger">*</span></label> --}}
                                <div class="form-group mb-2">
                                    <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                        </div>
                                        <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                        <input type="hidden" name="meta[business_statistics_items][icon][]" class="selected-files" value="{{ $business_statistics_items['icon'][$index] ?? '' }}" required>
                                    </div>
                                    <div class="file-preview box sm"></div>
                                </div>
                            </div>
                            <div class="col-md-4 form-group mb-2">
                                {{-- <label class="form-label">Value <span class="text-danger">*</span></label> --}}
                                <input type="text" name="meta[business_statistics_items][value][]" class="form-control" value="{{ $business_statistics_items['value'][$index] ?? '' }}" required>
                            </div>
                            <div class="col-md-4 form-group mb-2">
                                {{-- <label class="form-label">Title <span class="text-danger">*</span></label> --}}
                                <input type="text" name="meta[business_statistics_items][title][]" class="form-control" value="{{ $business_statistics_items['title'][$index] ?? '' }}" required>
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
        data-limit="20"
        data-content='
            <div class="row remove-parent">
                <div class="col-md-11">
                    <div class="row">
                        <div class="col-md-12">
                            <input value="data" name="meta[business_statistics_items][itration][]" type="hidden" required>
                        </div>
                        <div class="col-md-4">
                            {{-- <label class="form-label">Icon image <span class="text-danger">*</span></label> --}}
                            <div class="form-group mb-2">
                                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                    <input type="hidden" name="meta[business_statistics_items][icon][]" class="selected-files" required>
                                </div>
                                <div class="file-preview box sm"></div>
                            </div>
                        </div>
                        <div class="col-md-4 form-group mb-2">
                            {{-- <label class="form-label">Value <span class="text-danger">*</span></label> --}}
                            <input type="text" name="meta[business_statistics_items][value][]" class="form-control" placeholder="Enter value *" required>
                        </div>
                        <div class="col-md-4 form-group mb-2">
                            {{-- <label class="form-label">Title <span class="text-danger">*</span></label> --}}
                            <input type="text" name="meta[business_statistics_items][title][]" class="form-control" placeholder="Enter title *" required>
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
        data-target=".business-statistics-items-target"
    >
        <i class="ti ti-plus"></i>
        <span class="ml-2">Add More</span>
    </button>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Journey Section</h4>
    </div>

    <div class="journey-items-target col-md-12">
        @if(isset($journey_items['itration']) && is_array($journey_items['itration']))
            @foreach($journey_items['itration'] as $index => $itration)
                <div class="row remove-parent">
                    <div class="col-md-11">
                        <div class="row">
                            <div class="col-md-12">
                                <input value="{{ $index }}" name="meta[journey_items][itration][]" type="hidden" required>
                            </div>
                            <div class="col-md-4 form-group mb-2">
                                {{-- <label class="form-label">Year <span class="text-danger">*</span></label> --}}
                                <input type="text" name="meta[journey_items][year][]" class="form-control" value="{{ $journey_items['year'][$index] ?? '' }}" required>
                            </div>
                            <div class="col-md-4 form-group mb-2">
                                {{-- <label class="form-label">Title <span class="text-danger">*</span></label> --}}
                                <input type="text" name="meta[journey_items][title][]" class="form-control" value="{{ $journey_items['title'][$index] ?? '' }}" required>
                            </div>
                            <div class="col-md-4">
                                {{-- <label class="form-label">Image <span class="text-danger">*</span></label> --}}
                                <div class="form-group mb-2">
                                    <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                        </div>
                                        <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                        <input type="hidden" name="meta[journey_items][image][]" class="selected-files" value="{{ $journey_items['image'][$index] ?? '' }}" required>
                                    </div>
                                    <div class="file-preview box sm"></div>
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
        data-limit="20"
        data-content='
            <div class="row remove-parent">
                <div class="col-md-11">
                    <div class="row">
                        <div class="col-md-12">
                            <input value="data" name="meta[journey_items][itration][]" type="hidden" required>
                        </div>
                        <div class="col-md-4 form-group mb-2">
                            {{-- <label class="form-label">Year <span class="text-danger">*</span></label> --}}
                            <input type="text" name="meta[journey_items][year][]" class="form-control" placeholder="Enter year *" required>
                        </div>
                        <div class="col-md-4 form-group mb-2">
                            {{-- <label class="form-label">Title <span class="text-danger">*</span></label> --}}
                            <input type="text" name="meta[journey_items][title][]" class="form-control" placeholder="Enter title *" required>
                        </div>
                        <div class="col-md-4">
                            {{-- <label class="form-label">Image <span class="text-danger">*</span></label> --}}
                            <div class="form-group mb-2">
                                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                    <input type="hidden" name="meta[journey_items][image][]" class="selected-files" required>
                                </div>
                                <div class="file-preview box sm"></div>
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
        data-target=".journey-items-target"
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
        <input type="url" class="form-control" name="meta[video_url]" value="{{ $video_url }}" placeholder="Enter video URL" required>
    </div>
</div>
