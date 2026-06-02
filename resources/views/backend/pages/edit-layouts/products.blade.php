@php
    use App\Models\Page;

    // Breadcrumb Section
    $breadcrumb_image = $pageData->meta->where('meta_key', 'breadcrumb_image')->first()->meta_value ?? '';
    $breadcrumb_subtitle = $pageData->meta->where('meta_key', 'breadcrumb_subtitle')->first()->meta_value ?? '';
    $technical_sheet = $pageData->meta->where('meta_key', 'technical_sheet')->first()->meta_value ?? '';

    // Short Summery Section
    $short_summary_image = $pageData->meta->where('meta_key', 'short_summary_image')->first()->meta_value ?? '';
    $short_summary_description = $pageData->meta->where('meta_key', 'short_summary_description')->first()->meta_value ?? '';

    // Product Info Section
    $product_info_description = $pageData->meta->where('meta_key', 'product_info_description')->first()->meta_value ?? '';
    $product_info_items = json_decode($pageData->meta->where('meta_key', 'product_info_items')->first()->meta_value ?? '[]', true);
    $product_info_items = is_array($product_info_items) ? $product_info_items : [];

    // Relations Section (DB options)
    $layoutsCategory = ['product_category_detail_1','product_category_detail_2','product_category_detail_3','product_category_detail_4','product_category_detail_5'];
    $categoryPagesQuery = Page::query()->whereIn('layout', $layoutsCategory);
    $industryPagesQuery = Page::query()->where('layout', 'product_industry_detail');
    if (auth()->user()?->company_id) {
        $categoryPagesQuery->where('company_id', auth()->user()->company_id);
        $industryPagesQuery->where('company_id', auth()->user()->company_id);
    }
    $category_pages = $categoryPagesQuery->orderBy('title')->get(['id', 'title']);
    $industry_pages = $industryPagesQuery->orderBy('title')->get(['id', 'title']);

    $relation_category = $pageData->meta->where('meta_key', 'relation_category')->first()->meta_value ?? '';
    $relation_industries = json_decode($pageData->meta->where('meta_key', 'relation_industries')->first()->meta_value ?? '[]', true);
    $relation_industries = is_array($relation_industries) ? $relation_industries : [];
    $relation_type = $pageData->meta->where('meta_key', 'relation_type')->first()->meta_value ?? 'none';
    $relation_featured = $pageData->meta->where('meta_key', 'relation_featured')->first()->meta_value ?? 'no';

    // Sizes & Formats Section
    $sizes_formats = json_decode($pageData->meta->where('meta_key', 'sizes_formats')->first()->meta_value ?? '[]', true);
    $sizes_formats = is_array($sizes_formats) ? $sizes_formats : [];

    // Specification Section
    $specifications = json_decode($pageData->meta->where('meta_key', 'specifications')->first()->meta_value ?? '[]', true);
    $specifications = is_array($specifications) ? $specifications : [];

    // Compatibility Section
    $compatibility_description = $pageData->meta->where('meta_key', 'compatibility_description')->first()->meta_value ?? '';

    // Video Section
    $video_url = $pageData->meta->where('meta_key', 'video_url')->first()->meta_value ?? '';

    // Features Section
    $features_description = $pageData->meta->where('meta_key', 'features_description')->first()->meta_value ?? '';
    $features_items = json_decode($pageData->meta->where('meta_key', 'features_items')->first()->meta_value ?? '[]', true);
    $features_items = is_array($features_items) ? $features_items : [];

    // Accessories Section
    $accessories_items = json_decode($pageData->meta->where('meta_key', 'accessories_items')->first()->meta_value ?? '[]', true);
    $accessories_items = is_array($accessories_items) ? $accessories_items : [];
@endphp

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Breadcrumb Section</h4>
    </div>

    <div class="col-md-4 form-group mb-2">
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

    <div class="col-md-4 form-group mb-2">
        <label class="form-label">Subtitle</label>
        <input type="text" class="form-control" name="meta[breadcrumb_subtitle]" value="{{ $breadcrumb_subtitle }}">
    </div>

    <div class="col-md-4 form-group mb-2">
        <label class="form-label">Technical Sheet</label>
        <div class="form-group mb-2">
            <div class="input-group" data-toggle="aizuploader" data-type="document" data-multiple="false">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                </div>
                <div class="form-control file-amount">{{ __('Choose File') }}</div>
                <input value="{{ $technical_sheet }}" type="hidden" name="meta[technical_sheet]" class="selected-files">
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

    <div class="col-md-12">
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
        <input type="text" class="form-control" name="meta[short_summary_description]" value="{{ $short_summary_description }}" required>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Product Info Section</h4>
    </div>

    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Description <span class="text-danger">*</span></label>
        <textarea name="meta[product_info_description]" class="form-control" rows="4" required>{{ $product_info_description }}</textarea>
    </div>

    <div class="product-info-items-target col-md-12 d-none">
        @if(isset($product_info_items['itration']) && is_array($product_info_items['itration']))
            @foreach($product_info_items['itration'] as $index => $itration)
                <div class="row remove-parent">
                    <div class="col-md-11">
                        <div class="row">
                            <div class="col-md-12">
                                <input value="{{ $index }}" name="meta[product_info_items][itration][]" type="hidden">
                            </div>

                            <div class="col-md-12">
                                <div class="form-group mb-2">
                                    {{-- <label class="form-label">Industry <span class="text-danger">*</span></label> --}}
                                    <input
                                        value="{{ $product_info_items['industry'][$index] ?? '' }}"
                                        name="meta[product_info_items][industry][]"
                                        type="text"
                                        class="form-control"
                                        placeholder="Enter industry *"
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
        class="mt-1 btn btn-soft-success btn-icon w-100 d-none"
        data-toggle="add-more"
        data-limit="10"
        data-content='
            <div class="row remove-parent">
                <div class="col-md-11">
                    <div class="row">
                        <div class="col-md-12">
                            <input value="data" name="meta[product_info_items][itration][]" type="hidden" required>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group mb-2">
                                {{-- <label class="form-label">Industry <span class="text-danger">*</span></label> --}}
                                <input value="" name="meta[product_info_items][industry][]" type="text" class="form-control" placeholder="Enter industry *" required>
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
        data-target=".product-info-items-target"
    >
        <i class="ti ti-plus"></i>
        <span class="ml-2">Add More</span>
    </button>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Relations Section</h4>
    </div>

    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Category <span class="text-danger">*</span></label>
        <select class="form-control select2" name="meta[relation_category]" required>
            <option value="">Select Category</option>
            @foreach($category_pages as $row)
                <option value="{{ $row->id }}" {{ (string)$relation_category === (string)$row->id ? 'selected' : '' }}>
                    {{ $row->title }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Industry <span class="text-danger">*</span></label>
        <select class="form-control select2" name="meta[relation_industries][]" multiple required>
            @foreach($industry_pages as $row)
                <option value="{{ $row->id }}" {{ in_array($row->id, $relation_industries) ? 'selected' : '' }}>
                    {{ $row->title }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Type <span class="text-danger">*</span></label>
        <select class="form-control select2" name="meta[relation_type]" required>
            <option value="none" {{ $relation_type === 'none' ? 'selected' : '' }}>None</option>
            <option value="standard" {{ $relation_type === 'standard' ? 'selected' : '' }}>Standard</option>
            <option value="premium" {{ $relation_type === 'premium' ? 'selected' : '' }}>Premium</option>
        </select>
    </div>

    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Featured <span class="text-danger">*</span></label>
        <div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="meta[relation_featured]" id="relation_featured_yes" value="yes" {{ $relation_featured === 'yes' ? 'checked' : '' }} required>
                <label class="form-check-label" for="relation_featured_yes">Yes</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="meta[relation_featured]" id="relation_featured_no" value="no" {{ $relation_featured === 'no' ? 'checked' : '' }} required>
                <label class="form-check-label" for="relation_featured_no">No</label>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Sizes & Formats Section</h4>
    </div>

    <div class="sizes-formats-target col-md-12">
        @if(isset($sizes_formats['itration']) && is_array($sizes_formats['itration']))
            @foreach($sizes_formats['itration'] as $index => $itration)
                <div class="row remove-parent">
                    <div class="col-md-11">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <input value="{{ $index }}" name="meta[sizes_formats][itration][]" type="hidden" required>
                                    <input
                                        value="{{ $sizes_formats['variants'][$index] ?? '' }}"
                                        name="meta[sizes_formats][variants][]"
                                        type="text"
                                        class="form-control"
                                        placeholder="Enter variants *"
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
                                            name="meta[sizes_formats][image][]"
                                            class="selected-files"
                                            value="{{ $sizes_formats['image'][$index] ?? '' }}"
                                            required
                                        >
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
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <input value="data" name="meta[sizes_formats][itration][]" type="hidden" required>
                                <input value="" name="meta[sizes_formats][variants][]" type="text" class="form-control" placeholder="Enter variants *" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                    <input type="hidden" name="meta[sizes_formats][image][]" class="selected-files" required>
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
        data-target=".sizes-formats-target"
    >
        <i class="ti ti-plus"></i>
        <span class="ml-2">Add More</span>
    </button>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Specification Section</h4>
    </div>

    <div class="specifications-target col-md-12">
        @if(isset($specifications['itration']) && is_array($specifications['itration']))
            @foreach($specifications['itration'] as $index => $itration)
                <div class="row remove-parent">
                    <div class="col-md-11">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <input value="{{ $index }}" name="meta[specifications][itration][]" type="hidden" required>
                                    <input value="{{ $specifications['title'][$index] ?? '' }}" name="meta[specifications][title][]" type="text" class="form-control" placeholder="Title *" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <input value="{{ $specifications['description'][$index] ?? '' }}" name="meta[specifications][description][]" type="text" class="form-control" placeholder="Description *" required>
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
        data-limit="30"
        data-content='
            <div class="row remove-parent">
                <div class="col-md-11">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <input value="data" name="meta[specifications][itration][]" type="hidden" required>
                                <input value="" name="meta[specifications][title][]" type="text" class="form-control" placeholder="Title *" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <input value="" name="meta[specifications][description][]" type="text" class="form-control" placeholder="Description *" required>
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
        data-target=".specifications-target"
    >
        <i class="ti ti-plus"></i>
        <span class="ml-2">Add More</span>
    </button>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Compatibility Section</h4>
    </div>
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Description <span class="text-danger">*</span></label>
        <textarea name="meta[compatibility_description]" class="form-control text-editor" rows="4" required>{{ $compatibility_description }}</textarea>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Video Section</h4>
    </div>
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Video Url</label>
        <input type="url" class="form-control" name="meta[video_url]" value="{{ $video_url }}" placeholder="Enter video url">
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Features Section</h4>
    </div>

    <div class="col-md-12 form-group mb-2 d-none">
        <label class="form-label">Description</label>
        <textarea name="meta[features_description]" class="form-control text-editor" rows="4">{{ $features_description }}</textarea>
    </div>

    <div class="features-items-target col-md-12">
        @if(isset($features_items['itration']) && is_array($features_items['itration']))
            @foreach($features_items['itration'] as $index => $itration)
                <div class="row remove-parent">
                    <div class="col-md-11">
                        <div class="row">
                            <div class="col-md-12">
                                <input value="{{ $index }}" name="meta[features_items][itration][]" type="hidden" required>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                        </div>
                                        <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                        <input type="hidden" name="meta[features_items][image][]" class="selected-files" value="{{ $features_items['image'][$index] ?? '' }}" required>
                                    </div>
                                    <div class="file-preview box sm"></div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <input value="{{ $features_items['title'][$index] ?? '' }}" name="meta[features_items][title][]" type="text" class="form-control" placeholder="Title *" required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <input value="{{ $features_items['description'][$index] ?? '' }}" name="meta[features_items][description][]" type="text" class="form-control" placeholder="Description *" required>
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
                            <input value="data" name="meta[features_items][itration][]" type="hidden" required>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-2">
                                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                    <input type="hidden" name="meta[features_items][image][]" class="selected-files" required>
                                </div>
                                <div class="file-preview box sm"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-2">
                                <input value="" name="meta[features_items][title][]" type="text" class="form-control" placeholder="Title *" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-2">
                                <input value="" name="meta[features_items][description][]" type="text" class="form-control" placeholder="Description *" required>
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
        data-target=".features-items-target"
    >
        <i class="ti ti-plus"></i>
        <span class="ml-2">Add More</span>
    </button>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Accessories Section</h4>
    </div>

    <div class="accessories-items-target col-md-12">
        @if(isset($accessories_items['itration']) && is_array($accessories_items['itration']))
            @foreach($accessories_items['itration'] as $index => $itration)
                <div class="row remove-parent">
                    <div class="col-md-11">
                        <div class="row">
                            <div class="col-md-12">
                                <input value="{{ $index }}" name="meta[accessories_items][itration][]" type="hidden" required>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                        </div>
                                        <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                        <input type="hidden" name="meta[accessories_items][image][]" class="selected-files" value="{{ $accessories_items['image'][$index] ?? '' }}" required>
                                    </div>
                                    <div class="file-preview box sm"></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <input value="{{ $accessories_items['title'][$index] ?? '' }}" name="meta[accessories_items][title][]" type="text" class="form-control" placeholder="Title *" required>
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
                            <input value="data" name="meta[accessories_items][itration][]" type="hidden" required>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                    <input type="hidden" name="meta[accessories_items][image][]" class="selected-files" required>
                                </div>
                                <div class="file-preview box sm"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <input value="" name="meta[accessories_items][title][]" type="text" class="form-control" placeholder="Title *" required>
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
        data-target=".accessories-items-target"
    >
        <i class="ti ti-plus"></i>
        <span class="ml-2">Add More</span>
    </button>
</div>

