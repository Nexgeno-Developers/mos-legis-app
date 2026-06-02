@php
    use App\Models\Page;

    // Banner Section (stored as JSON repeater: meta[banner_items])
    $banner_items = json_decode(
        $pageData->meta->where('meta_key', 'banner_items')->first()->meta_value ?? '[]',
        true
    );
    $banner_items = is_array($banner_items) ? $banner_items : [];

    // Approach Section
    $approach_title = $pageData->meta->where('meta_key', 'approach_title')->first()->meta_value ?? '';
    $approach_subtitle = $pageData->meta->where('meta_key', 'approach_subtitle')->first()->meta_value ?? '';
    $approach_image = $pageData->meta->where('meta_key', 'approach_image')->first()->meta_value ?? '';
    $approach_short_description = $pageData->meta->where('meta_key', 'approach_short_description')->first()->meta_value ?? '';
    $approach_question_1 = $pageData->meta->where('meta_key', 'approach_question_1')->first()->meta_value ?? '';
    $approach_question_2 = $pageData->meta->where('meta_key', 'approach_question_2')->first()->meta_value ?? '';
    $approach_question_3 = $pageData->meta->where('meta_key', 'approach_question_3')->first()->meta_value ?? '';
    $approach_production_scale = $pageData->meta->where('meta_key', 'approach_production_scale')->first()->meta_value ?? '';
    $approach_market_region = $pageData->meta->where('meta_key', 'approach_market_region')->first()->meta_value ?? '';
    $approach_navigation_url = $pageData->meta->where('meta_key', 'approach_navigation_url')->first()->meta_value ?? '';

    $selected_product_industries = json_decode(
        $pageData->meta->where('meta_key', 'approach_product_industries')->first()->meta_value ?? '[]',
        true
    );
    $selected_product_industries = is_array($selected_product_industries) ? $selected_product_industries : [];

    $productIndustryQuery = Page::query()->where('layout', 'product_industry_detail');
    if (auth()->user()?->company_id) {
        $productIndustryQuery->where('company_id', auth()->user()->company_id);
    }
    $product_industry_pages = $productIndustryQuery->orderBy('title')->get(['id', 'title']);

    // Global beverage Section
    $global_beverage_title = $pageData->meta->where('meta_key', 'global_beverage_title')->first()->meta_value ?? '';
    $global_beverage_description = $pageData->meta->where('meta_key', 'global_beverage_description')->first()->meta_value ?? '';
    $global_beverage_image = $pageData->meta->where('meta_key', 'global_beverage_image')->first()->meta_value ?? '';
    $global_beverage_video_url = $pageData->meta->where('meta_key', 'global_beverage_video_url')->first()->meta_value ?? '';

    // FAQs Section (stored as JSON)
    $faqs_items = json_decode($pageData->meta->where('meta_key', 'faqs_items')->first()->meta_value ?? '[]', true);
    $faqs_items = is_array($faqs_items) ? $faqs_items : [];
@endphp

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Banner Section</h4>
    </div>

    <div class="banner-items-target col-md-12">
        @if(isset($banner_items['itration']) && is_array($banner_items['itration']))
            @foreach($banner_items['itration'] as $index => $itration)
                <div class="row remove-parent">
                    <div class="col-md-11">
                        <div class="row">
                            <div class="col-md-12">
                                <input value="{{ $index }}" name="meta[banner_items][itration][]" type="hidden" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Desktop banner <span class="text-danger">*</span></label>
                                <div class="form-group mb-2">
                                    <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                        </div>
                                        <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                        <input value="{{ $banner_items['desktop_banner'][$index] ?? '' }}" type="hidden" name="meta[banner_items][desktop_banner][]" class="selected-files" required>
                                    </div>
                                    <div class="file-preview box sm"></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Mobile banner <span class="text-danger">*</span></label>
                                <div class="form-group mb-2">
                                    <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                        </div>
                                        <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                        <input value="{{ $banner_items['mobile_banner'][$index] ?? '' }}" type="hidden" name="meta[banner_items][mobile_banner][]" class="selected-files" required>
                                    </div>
                                    <div class="file-preview box sm"></div>
                                </div>
                            </div>

                            <div class="col-md-6 form-group mb-2">
                                <label class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="meta[banner_items][title][]" value="{{ $banner_items['title'][$index] ?? '' }}" required>
                            </div>

                            <div class="col-md-6 form-group mb-2">
                                <label class="form-label">Subtitle <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="meta[banner_items][subtitle][]" value="{{ $banner_items['subtitle'][$index] ?? '' }}" required>
                            </div>

                            <div class="col-md-6 form-group mb-2">
                                <label class="form-label">Navigation Url <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="meta[banner_items][navigation_url][]" value="{{ $banner_items['navigation_url'][$index] ?? '' }}" required>
                            </div>

                            <div class="col-md-6 form-group mb-2">
                                <label class="form-label">Label <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="meta[banner_items][label][]" value="{{ $banner_items['label'][$index] ?? '' }}" required>
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
                            <input value="data" name="meta[banner_items][itration][]" type="hidden" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Desktop banner <span class="text-danger">*</span></label>
                            <div class="form-group mb-2">
                                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                    <input type="hidden" name="meta[banner_items][desktop_banner][]" class="selected-files" required>
                                </div>
                                <div class="file-preview box sm"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mobile banner <span class="text-danger">*</span></label>
                            <div class="form-group mb-2">
                                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                    <input type="hidden" name="meta[banner_items][mobile_banner][]" class="selected-files" required>
                                </div>
                                <div class="file-preview box sm"></div>
                            </div>
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="meta[banner_items][title][]" class="form-control" placeholder="Enter title" required>
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <label class="form-label">Subtitle <span class="text-danger">*</span></label>
                            <input type="text" name="meta[banner_items][subtitle][]" class="form-control" placeholder="Enter subtitle" required>
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <label class="form-label">Navigation Url <span class="text-danger">*</span></label>
                            <input type="text" name="meta[banner_items][navigation_url][]" class="form-control" placeholder="Enter navigation url" required>
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <label class="form-label">Label <span class="text-danger">*</span></label>
                            <input type="text" name="meta[banner_items][label][]" class="form-control" placeholder="Enter label" required>
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
        data-target=".banner-items-target"
    >
        <i class="ti ti-plus"></i>
        <span class="ml-2">Add More</span>
    </button>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Approach Section</h4>
    </div>

    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[approach_title]" value="{{ $approach_title }}" required>
    </div>

    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Subtitle <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[approach_subtitle]" value="{{ $approach_subtitle }}" required>
    </div>

    <div class="col-md-6">
        <label class="form-label">Image <span class="text-danger">*</span></label>
        <div class="form-group mb-2">
            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                </div>
                <div class="form-control file-amount">{{ __('Choose File') }}</div>
                <input value="{{ $approach_image }}" type="hidden" name="meta[approach_image]" class="selected-files" required>
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div>

    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Short Description <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[approach_short_description]" value="{{ $approach_short_description }}" required>
    </div>

    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Question 1 <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[approach_question_1]" value="{{ $approach_question_1 }}" required>
    </div>

    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Product Industry <span class="text-danger">*</span></label>
        <select class="form-control select2" name="meta[approach_product_industries][]" multiple required>
            @foreach($product_industry_pages as $row)
                <option value="{{ $row->id }}" {{ in_array($row->id, $selected_product_industries) ? 'selected' : '' }}>
                    {{ $row->title }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Question 2 <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[approach_question_2]" value="{{ $approach_question_2 }}" required>
    </div>

    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Production Scale <span class="text-danger">*</span></label>
        <input type="text" class="form-control aiz-tag-input" name="meta[approach_production_scale]" value="{{ $approach_production_scale }}" placeholder="Enter tags separated by commas" required>
    </div>

    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Question 3 <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[approach_question_3]" value="{{ $approach_question_3 }}" required>
    </div>

    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Market Region <span class="text-danger">*</span></label>
        <input type="text" class="form-control aiz-tag-input" name="meta[approach_market_region]" value="{{ $approach_market_region }}" placeholder="Enter tags separated by commas" required>
    </div>

    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Navigation Url <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[approach_navigation_url]" value="{{ $approach_navigation_url }}" required>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Global beverage Section</h4>
    </div>

    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input class="form-control" value="{{ $global_beverage_title }}" name="meta[global_beverage_title]" type="text" required>
    </div>

    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Description <span class="text-danger">*</span></label>
        <input class="form-control" value="{{ $global_beverage_description }}" name="meta[global_beverage_description]" type="text" required>
    </div>

    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Image <span class="text-danger">*</span></label>
        <div class="form-group mb-2">
            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                </div>
                <div class="form-control file-amount">{{ __('Choose File') }}</div>
                <input value="{{ $global_beverage_image ?? '' }}" type="hidden" name="meta[global_beverage_image]" class="selected-files" required>

            </div>
            <div class="file-preview box sm"></div>
        </div>


    </div>

    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Video Url <span class="text-danger">*</span></label>
        <input class="form-control" value="{{ $global_beverage_video_url }}" name="meta[global_beverage_video_url]" type="text" required>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <div class="text-center">
            <h4 class="text-primary mb-0">Services Section</h4>
            <p class="mb-0">System will fetch services automatically.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <div class="text-center">
            <h4 class="text-primary mb-0">Latest Insight Section</h4>
            <p class="mb-0">System will fetch insights automatically.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <div class="text-center">
            <h4 class="text-primary mb-0">Product Sustainability Section</h4>
            <p class="mb-0">System will fetch sustainability, products, automatically.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <div class="text-center">
            <h4 class="text-primary mb-0">Contribution Sustainability Section</h4>
            <p class="mb-0">System will fetch sustainability block's automatically.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <div class="text-center">
            <h4 class="text-primary mb-0">Latest Press Release Section</h4>
            <p class="mb-0">System will fetch press release blocks automatically.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">FAQS Section</h4>
    </div>

    <div class="faqs-items-target col-md-12">
        @if(isset($faqs_items['itration']) && is_array($faqs_items['itration']))
            @foreach($faqs_items['itration'] as $index => $itration)
                <div class="row remove-parent">
                    <div class="col-md-11">
                        <div class="row">
                            <div class="col-md-12">
                                <input value="{{ $index }}" name="meta[faqs_items][itration][]" type="hidden" required>
                            </div>
                            <div class="col-md-12 form-group mb-2">
                                {{-- <label class="form-label">Title <span class="text-danger">*</span></label> --}}
                                <input value="{{ $faqs_items['title'][$index] ?? '' }}" name="meta[faqs_items][title][]" type="text" class="form-control" placeholder="Enter title" required>
                            </div>
                            <div class="col-md-12 form-group mb-2">
                                {{-- <label class="form-label">Description <span class="text-danger">*</span></label> --}}
                                <textarea name="meta[faqs_items][description][]" class="form-control text-editor" rows="3" required>{{ $faqs_items['description'][$index] ?? '' }}</textarea>
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
                            <input value="data" name="meta[faqs_items][itration][]" type="hidden" required>
                        </div>
                        <div class="col-md-12 form-group mb-2">
                            {{-- <label class="form-label">Title <span class="text-danger">*</span></label> --}}
                            <input value="" name="meta[faqs_items][title][]" type="text" class="form-control" placeholder="Enter title" required>
                        </div>
                        <div class="col-md-12 form-group mb-2">
                            {{-- <label class="form-label">Description <span class="text-danger">*</span></label> --}}
                            <textarea name="meta[faqs_items][description][]" class="form-control text-editor" rows="3" placeholder="Enter description" required></textarea>
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
        data-target=".faqs-items-target"
    >
        <i class="ti ti-plus"></i>
        <span class="ml-2">Add More</span>
    </button>
</div>
