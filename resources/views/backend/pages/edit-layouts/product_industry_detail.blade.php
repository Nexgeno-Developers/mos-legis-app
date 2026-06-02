@php
    use App\Models\Page;

    // Breadcrumb section
    $breadcrumb_image = $pageData->meta->where('meta_key', 'breadcrumb_image')->first()->meta_value ?? '';

    // Short summary section
    $short_summary_icon = $pageData->meta->where('meta_key', 'short_summary_icon')->first()->meta_value ?? '';
    $short_summary_title = $pageData->meta->where('meta_key', 'short_summary_title')->first()->meta_value ?? '';
    $short_summary_description = $pageData->meta->where('meta_key', 'short_summary_description')->first()->meta_value ?? '';

    // Support section
    $support_title = $pageData->meta->where('meta_key', 'support_title')->first()->meta_value ?? '';
    $support_subtitle = $pageData->meta->where('meta_key', 'support_subtitle')->first()->meta_value ?? '';
    $support_description = $pageData->meta->where('meta_key', 'support_description')->first()->meta_value ?? '';

    $support_items = json_decode(
        $pageData->meta->where('meta_key', 'support_items')->first()->meta_value ?? '[]',
        true
    );
    $support_items = is_array($support_items) ? $support_items : [];

    // Pilot Plant section
    $pilot_plant_title = $pageData->meta->where('meta_key', 'pilot_plant_title')->first()->meta_value ?? '';
    $pilot_plant_subtitle = $pageData->meta->where('meta_key', 'pilot_plant_subtitle')->first()->meta_value ?? '';
    $pilot_plant_image = $pageData->meta->where('meta_key', 'pilot_plant_image')->first()->meta_value ?? '';
    $pilot_plant_navigation_url = $pageData->meta->where('meta_key', 'pilot_plant_navigation_url')->first()->meta_value ?? '';

    // Leading section
    $leading_title = $pageData->meta->where('meta_key', 'leading_title')->first()->meta_value ?? '';
    $leading_subtitle = $pageData->meta->where('meta_key', 'leading_subtitle')->first()->meta_value ?? '';
    $leading_image = $pageData->meta->where('meta_key', 'leading_image')->first()->meta_value ?? '';

    $leading_items = json_decode(
        $pageData->meta->where('meta_key', 'leading_items')->first()->meta_value ?? '[]',
        true
    );
    $leading_items = is_array($leading_items) ? $leading_items : [];

    // Latest insights

    // Recommended products section
    $recommended_products = json_decode(
        $pageData->meta->where('meta_key', 'recommended_products')->first()->meta_value ?? '[]',
        true
    );
    $recommended_products = is_array($recommended_products) ? $recommended_products : [];

    $recommended_products_pages = Page::query()
        ->select(['id', 'title'])
        ->where('layout', 'products')
        ->where('company_id', $pageData->company_id)
        ->orderBy('title')
        ->get();
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
        <label for="short_summary_title" class="form-label">Title <span class="text-danger">*</span></label>
        <input id="short_summary_title" class="form-control" name="meta[short_summary_title]" type="text" value="{{ $short_summary_title }}" required>
    </div>

    <div class="col-md-12 form-group mb-2">
        <label for="short_summary_description" class="form-label">Short Description <span class="text-danger">*</span></label>
        <input id="short_summary_description" class="form-control" name="meta[short_summary_description]" type="text" value="{{ $short_summary_description }}" required>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Support Section</h4>
    </div>

    <div class="col-md-6 form-group mb-2">
        <label for="support_title" class="form-label">Title <span class="text-danger">*</span></label>
        <input id="support_title" class="form-control" name="meta[support_title]" type="text" value="{{ $support_title }}" required>
    </div>

    <div class="col-md-6 form-group mb-2">
        <label for="support_subtitle" class="form-label">Subtitle <span class="text-danger">*</span></label>
        <input id="support_subtitle" name="meta[support_subtitle]" class="form-control" rows="4" value="{{ $support_subtitle }}" required>
    </div>

    <div class="col-md-12 form-group mb-2">
        <label for="support_description" class="form-label">Description <span class="text-danger">*</span></label>
        <textarea id="support_description" name="meta[support_description]" class="form-control" rows="4" required>{{ $support_description }}</textarea>
    </div>

    <div class="col-md-12">
        <hr>
    </div>

    <div class="support-items-target col-md-12">
        @if(isset($support_items['itration']) && is_array($support_items['itration']))
            @foreach($support_items['itration'] as $index => $itration)
                <div class="row remove-parent">
                    <div class="col-md-11">
                        <div class="row">
                            <div class="col-md-12">
                                <input value="{{ $index }}" name="meta[support_items][itration][]" type="hidden" required>
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
                                            name="meta[support_items][image][]"
                                            class="selected-files"
                                            value="{{ $support_items['image'][$index] ?? '' }}"
                                            required
                                        >
                                    </div>
                                    <div class="file-preview box sm"></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <input
                                        value="{{ $support_items['title'][$index] ?? '' }}"
                                        name="meta[support_items][title][]"
                                        type="text"
                                        class="form-control"
                                        minlength="3"
                                        maxlength="200"
                                        placeholder="Enter title *"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group mb-2">
                                    <textarea
                                        name="meta[support_items][description][]"
                                        class="form-control"
                                        rows="2"
                                        required
                                    >{{ $support_items['description'][$index] ?? '' }}</textarea>
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
        class="mt-1 btn btn-soft-success btn-icon w-100 support-items-add-more"
        data-toggle="add-more"
        {{-- data-limit="5" --}}
        data-content='
            <div class="row remove-parent">
                <div class="col-md-11">
                    <div class="row">

                        <div class="col-md-12">
                            <input value="data" name="meta[support_items][itration][]" type="hidden" required>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                    <input type="hidden" name="meta[support_items][image][]" class="selected-files" required>
                                </div>
                                <div class="file-preview box sm"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <input value="" name="meta[support_items][title][]" type="text" class="form-control" minlength="3" maxlength="200" placeholder="Enter title *" required>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group mb-2">
                                <textarea name="meta[support_items][description][]" class="form-control" rows="2" placeholder="Enter description *" required></textarea>
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
        data-target=".support-items-target"
    >
        <i class="ti ti-plus"></i>
        <span class="ml-2">Add More</span>
    </button>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Pilot Plant Section</h4>
    </div>

    <div class="col-md-6 form-group mb-2">
        <label for="pilot_plant_title" class="form-label">Title <span class="text-danger">*</span></label>
        <input id="pilot_plant_title" class="form-control" name="meta[pilot_plant_title]" type="text" value="{{ $pilot_plant_title }}" required>
    </div>

    <div class="col-md-6 form-group mb-2">
        <label for="pilot_plant_subtitle" class="form-label">Subbitle <span class="text-danger">*</span></label>
        <input id="pilot_plant_subtitle" class="form-control" name="meta[pilot_plant_subtitle]" type="text" value="{{ $pilot_plant_subtitle }}" required>
    </div>

    <div class="col-md-6">
        <label for="pilot_plant_image" class="form-label">Image <span class="text-danger">*</span></label>
        <div class="form-group mb-2">
            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                </div>
                <div class="form-control file-amount">{{ __('Choose File') }}</div>
                <input value="{{ $pilot_plant_image }}" type="hidden" name="meta[pilot_plant_image]" class="selected-files" required>
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div>

    <div class="col-md-6 form-group mb-2">
        <label for="pilot_plant_navigation_url" class="form-label">Navigation Url <span class="text-danger">*</span></label>
        <input id="pilot_plant_navigation_url" class="form-control" name="meta[pilot_plant_navigation_url]" type="text" value="{{ $pilot_plant_navigation_url }}" required>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Leading Section</h4>
    </div>

    <div class="col-md-4 form-group mb-2">
        <label for="leading_title" class="form-label">Title <span class="text-danger">*</span></label>
        <input id="leading_title" class="form-control" name="meta[leading_title]" type="text" value="{{ $leading_title }}" required>
    </div>

    <div class="col-md-4 form-group mb-2">
        <label for="leading_subtitle" class="form-label">Subtitle <span class="text-danger">*</span></label>
        <input id="leading_subtitle" class="form-control" name="meta[leading_subtitle]" type="text" value="{{ $leading_subtitle }}" required>
    </div>

    <div class="col-md-4 form-group mb-2">
        <label for="leading_image" class="form-label">Image <span class="text-danger">*</span></label>
        <div class="form-group mb-2">
            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                </div>
                <div class="form-control file-amount">{{ __('Choose File') }}</div>
                <input value="{{ $leading_image }}" type="hidden" name="meta[leading_image]" class="selected-files" required>
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div>

    <div class="col-md-12">
        <hr>
    </div>

    <div class="leading-items-target col-md-12">
        @if(isset($leading_items['itration']) && is_array($leading_items['itration']))
            @foreach($leading_items['itration'] as $index => $itration)
                <div class="row remove-parent">
                    <div class="col-md-11">
                        <div class="row">
                            <div class="col-md-12">
                                <input value="{{ $index }}" name="meta[leading_items][itration][]" type="hidden" required>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group mb-2">
                                    <input
                                        value="{{ $leading_items['key_points'][$index] ?? '' }}"
                                        name="meta[leading_items][key_points][]"
                                        type="text"
                                        class="form-control"
                                        minlength="3"
                                        maxlength="300"
                                        placeholder="Enter key point *"
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
        class="mt-1 btn btn-soft-success btn-icon w-100 leading-items-add-more"
        data-toggle="add-more"
        {{-- data-limit="5" --}}
        data-content='
            <div class="row remove-parent">
                <div class="col-md-11">
                    <div class="row">
                        <div class="col-md-12">
                            <input value="data" name="meta[leading_items][itration][]" type="hidden" required>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group mb-2">
                                <input value="" name="meta[leading_items][key_points][]" type="text" class="form-control" minlength="3" maxlength="300" placeholder="Enter key point *" required>
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
        data-target=".leading-items-target"
    >
        <i class="ti ti-plus"></i>
        <span class="ml-2">Add More</span>
    </button>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <div class="text-center">
            <h4 class="text-primary mb-0">Latest Insights Section</h4>
            <p class="mb-0">The system will automatically fetch Latest Insights.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Recommended Products Section</h4>
    </div>

    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Product <span class="text-danger">*</span></label>
        <select class="form-control select2" name="meta[recommended_products][]" multiple>
            @foreach($recommended_products_pages as $p)
                <option value="{{ $p->id }}" {{ in_array($p->id, $recommended_products) ? 'selected' : '' }}>
                    {{ $p->title }}
                </option>
            @endforeach
        </select>
    </div>
</div>
