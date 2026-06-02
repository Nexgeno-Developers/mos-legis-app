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

    $hero_title = $pageData->meta->where('meta_key', 'hero_title')->first()->meta_value ?? '';
    $hero_subtitle = $pageData->meta->where('meta_key', 'hero_subtitle')->first()->meta_value ?? '';
    $hero_description = $pageData->meta->where('meta_key', 'hero_description')->first()->meta_value ?? '';
    $hero_items = json_decode($pageData->meta->where('meta_key', 'hero_items')->first()->meta_value ?? '[]', true);
    $hero_items = is_array($hero_items) ? $hero_items : [];

    $governance_title = $pageData->meta->where('meta_key', 'governance_title')->first()->meta_value ?? '';
    $governance_subtitle = $pageData->meta->where('meta_key', 'governance_subtitle')->first()->meta_value ?? '';
    $governance_description = $pageData->meta->where('meta_key', 'governance_description')->first()->meta_value ?? '';
    $governance_items = json_decode($pageData->meta->where('meta_key', 'governance_items')->first()->meta_value ?? '[]', true);
    $governance_items = is_array($governance_items) ? $governance_items : [];

    $ethical_title = $pageData->meta->where('meta_key', 'ethical_title')->first()->meta_value ?? '';
    $ethical_subtitle = $pageData->meta->where('meta_key', 'ethical_subtitle')->first()->meta_value ?? '';
    $ethical_banner = $pageData->meta->where('meta_key', 'ethical_banner')->first()->meta_value ?? '';
    $ethical_tagline = $pageData->meta->where('meta_key', 'ethical_tagline')->first()->meta_value ?? '';
    $ethical_url = $pageData->meta->where('meta_key', 'ethical_url')->first()->meta_value ?? '';
    $ethical_description = $pageData->meta->where('meta_key', 'ethical_description')->first()->meta_value ?? '';

    $risk_control_title = $pageData->meta->where('meta_key', 'risk_control_title')->first()->meta_value ?? '';
    $risk_control_subtitle = $pageData->meta->where('meta_key', 'risk_control_subtitle')->first()->meta_value ?? '';
    $risk_control_description = $pageData->meta->where('meta_key', 'risk_control_description')->first()->meta_value ?? '';
    $risk_control_items = json_decode($pageData->meta->where('meta_key', 'risk_control_items')->first()->meta_value ?? '[]', true);
    $risk_control_items = is_array($risk_control_items) ? $risk_control_items : [];

    $global_standard_title = $pageData->meta->where('meta_key', 'global_standard_title')->first()->meta_value ?? '';
    $global_standard_subtitle = $pageData->meta->where('meta_key', 'global_standard_subtitle')->first()->meta_value ?? '';
    $global_standard_image = $pageData->meta->where('meta_key', 'global_standard_image')->first()->meta_value ?? '';
    $global_standard_description = $pageData->meta->where('meta_key', 'global_standard_description')->first()->meta_value ?? '';
    $global_standard_items = json_decode($pageData->meta->where('meta_key', 'global_standard_items')->first()->meta_value ?? '[]', true);
    $global_standard_items = is_array($global_standard_items) ? $global_standard_items : [];

    $digital_trust_title = $pageData->meta->where('meta_key', 'digital_trust_title')->first()->meta_value ?? '';
    $digital_trust_subtitle = $pageData->meta->where('meta_key', 'digital_trust_subtitle')->first()->meta_value ?? '';
    $digital_trust_image = $pageData->meta->where('meta_key', 'digital_trust_image')->first()->meta_value ?? '';
    $digital_trust_description = $pageData->meta->where('meta_key', 'digital_trust_description')->first()->meta_value ?? '';
    $digital_trust_items = json_decode($pageData->meta->where('meta_key', 'digital_trust_items')->first()->meta_value ?? '[]', true);
    $digital_trust_items = is_array($digital_trust_items) ? $digital_trust_items : [];
    $digital_trust_type2_items = json_decode($pageData->meta->where('meta_key', 'digital_trust_type2_items')->first()->meta_value ?? '[]', true);
    $digital_trust_type2_items = is_array($digital_trust_type2_items) ? $digital_trust_type2_items : [];

    $speak_up_title = $pageData->meta->where('meta_key', 'speak_up_title')->first()->meta_value ?? '';
    $speak_up_subtitle = $pageData->meta->where('meta_key', 'speak_up_subtitle')->first()->meta_value ?? '';
    $speak_up_description = $pageData->meta->where('meta_key', 'speak_up_description')->first()->meta_value ?? '';
    $speak_up_items = json_decode($pageData->meta->where('meta_key', 'speak_up_items')->first()->meta_value ?? '[]', true);
    $speak_up_items = is_array($speak_up_items) ? $speak_up_items : [];
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
        <h4 class="text-primary">Hero Section</h4>
    </div>
    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[hero_title]" value="{{ $hero_title }}" required>
    </div>
    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Subtitle <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[hero_subtitle]" value="{{ $hero_subtitle }}" required>
    </div>
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Description <span class="text-danger">*</span></label>
        <textarea name="meta[hero_description]" class="form-control text-editor" rows="4" required>{{ $hero_description }}</textarea>
    </div>

    <div class="hero-items-target col-md-12">
        @if(isset($hero_items['itration']) && is_array($hero_items['itration']))
            @foreach($hero_items['itration'] as $index => $itration)
                <div class="row remove-parent">
                    <div class="col-md-11">
                        <div class="row">
                            <div class="col-md-12"><input value="{{ $index }}" name="meta[hero_items][itration][]" type="hidden" required></div>
                            <div class="col-md-6 form-group mb-2">
                                {{-- <label class="form-label">Icon <span class="text-danger">*</span></label> --}}
                                <div class="form-group mb-2">
                                    <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                        <div class="input-group-prepend"><div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div></div>
                                        <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                        <input type="hidden" name="meta[hero_items][icon][]" class="selected-files" value="{{ $hero_items['icon'][$index] ?? '' }}" required>
                                    </div>
                                    <div class="file-preview box sm"></div>
                                </div>
                            </div>
                            <div class="col-md-6 form-group mb-2">
                                {{-- <label class="form-label">Title <span class="text-danger">*</span></label> --}}
                                <input type="text" name="meta[hero_items][title][]" class="form-control" value="{{ $hero_items['title'][$index] ?? '' }}" required>
                            </div>
                            <div class="col-md-12 form-group mb-2">
                                <label class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea name="meta[hero_items][description][]" class="form-control" rows="3" required>{{ $hero_items['description'][$index] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1 btn-dynamic-fields">
                        <button type="button" class="btn btn-icon btn-circle btn-soft-danger" data-toggle="remove-parent" data-parent=".remove-parent"><i class="ti ti-x"></i></button>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
    <button type="button" class="mt-1 btn btn-soft-success btn-icon w-100" data-toggle="add-more" data-limit="15" data-content='
            <div class="row remove-parent">
                <div class="col-md-11">
                    <div class="row">
                        <div class="col-md-12"><input value="data" name="meta[hero_items][itration][]" type="hidden" required></div>
                        <div class="col-md-6 form-group mb-2">
                            {{-- <label class="form-label">Icon <span class="text-danger">*</span></label> --}}
                            <div class="form-group mb-2">
                                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                    <div class="input-group-prepend"><div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div></div>
                                    <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                    <input type="hidden" name="meta[hero_items][icon][]" class="selected-files" required>
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
                            <textarea name="meta[hero_items][description][]" class="form-control" rows="3" placeholder="Enter description *" required></textarea>
                        </div>
                    </div>
                </div>
                <div class="col-md-1 btn-dynamic-fields">
                    <button type="button" class="btn btn-icon btn-circle btn-soft-danger" data-toggle="remove-parent" data-parent=".remove-parent"><i class="ti ti-x"></i></button>
                </div>
            </div>
        ' data-target=".hero-items-target">
        <i class="ti ti-plus"></i><span class="ml-2">Add More</span>
    </button>
</div>

<div class="row">
    <div class="col-md-12"><hr><h4 class="text-primary">Governance Framework Section</h4></div>
    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[governance_title]" value="{{ $governance_title }}" required>
    </div>
    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Subtitle <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[governance_subtitle]" value="{{ $governance_subtitle }}" required>
    </div>
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Description <span class="text-danger">*</span></label>
        <textarea name="meta[governance_description]" class="form-control text-editor" rows="4" required>{{ $governance_description }}</textarea>
    </div>
    <div class="governance-items-target col-md-12">
        @if(isset($governance_items['itration']) && is_array($governance_items['itration']))
            @foreach($governance_items['itration'] as $index => $itration)
                <div class="row remove-parent">
                    <div class="col-md-11">
                        <div class="row">
                            <div class="col-md-12"><input value="{{ $index }}" name="meta[governance_items][itration][]" type="hidden" required></div>
                            <div class="col-md-6 form-group mb-2">
                                {{-- <label class="form-label">Icon <span class="text-danger">*</span></label> --}}
                                <div class="form-group mb-2">
                                    <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                        <div class="input-group-prepend"><div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div></div>
                                        <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                        <input type="hidden" name="meta[governance_items][icon][]" class="selected-files" value="{{ $governance_items['icon'][$index] ?? '' }}" required>
                                    </div>
                                    <div class="file-preview box sm"></div>
                                </div>
                            </div>
                            <div class="col-md-6 form-group mb-2">
                                {{-- <label class="form-label">Title <span class="text-danger">*</span></label> --}}
                                <input type="text" name="meta[governance_items][title][]" class="form-control" value="{{ $governance_items['title'][$index] ?? '' }}" required>
                            </div>
                            <div class="col-md-12 form-group mb-2">
                                {{-- <label class="form-label">Description <span class="text-danger">*</span></label> --}}
                                <textarea name="meta[governance_items][description][]" class="form-control" rows="3" required>{{ $governance_items['description'][$index] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1 btn-dynamic-fields">
                        <button type="button" class="btn btn-icon btn-circle btn-soft-danger" data-toggle="remove-parent" data-parent=".remove-parent"><i class="ti ti-x"></i></button>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
    <button type="button" class="mt-1 btn btn-soft-success btn-icon w-100" data-toggle="add-more" data-limit="15" data-content='
            <div class="row remove-parent">
                <div class="col-md-11">
                    <div class="row">
                        <div class="col-md-12"><input value="data" name="meta[governance_items][itration][]" type="hidden" required></div>
                        <div class="col-md-6 form-group mb-2">
                            {{-- <label class="form-label">Icon <span class="text-danger">*</span></label> --}}
                            <div class="form-group mb-2">
                                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                    <div class="input-group-prepend"><div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div></div>
                                    <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                    <input type="hidden" name="meta[governance_items][icon][]" class="selected-files" required>
                                </div>
                                <div class="file-preview box sm"></div>
                            </div>
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            {{-- <label class="form-label">Title <span class="text-danger">*</span></label> --}}
                            <input type="text" name="meta[governance_items][title][]" class="form-control" placeholder="Enter title *" required>
                        </div>
                        <div class="col-md-12 form-group mb-2">
                            {{-- <label class="form-label">Description <span class="text-danger">*</span></label> --}}
                            <textarea name="meta[governance_items][description][]" class="form-control" rows="3" placeholder="Enter description *" required></textarea>
                        </div>
                    </div>
                </div>
                <div class="col-md-1 btn-dynamic-fields">
                    <button type="button" class="btn btn-icon btn-circle btn-soft-danger" data-toggle="remove-parent" data-parent=".remove-parent"><i class="ti ti-x"></i></button>
                </div>
            </div>
        ' data-target=".governance-items-target">
        <i class="ti ti-plus"></i><span class="ml-2">Add More</span>
    </button>
</div>

<div class="row">
    <div class="col-md-12"><hr><h4 class="text-primary">Ethical Standards Section</h4></div>
    <div class="col-md-4 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[ethical_title]" value="{{ $ethical_title }}" required>
    </div>

    <div class="col-md-4 form-group mb-2">
        <label class="form-label">Subtitle <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[ethical_subtitle]" value="{{ $ethical_subtitle }}" required>
    </div>


    <div class="col-md-4 form-group mb-2">
        <label class="form-label">Banner <span class="text-danger">*</span></label>
        <div class="form-group mb-2">
            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                <div class="input-group-prepend"><div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div></div>
                <div class="form-control file-amount">{{ __('Choose File') }}</div>
                <input value="{{ $ethical_banner }}" type="hidden" name="meta[ethical_banner]" class="selected-files" required>
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div>
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Tagline <span class="text-danger">*</span></label>
        <textarea name="meta[ethical_tagline]" class="form-control text-editor" rows="3" required>{{ $ethical_tagline }}</textarea>
    </div>
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">URL <span class="text-danger">*</span></label>
        <input type="url" class="form-control" name="meta[ethical_url]" value="{{ $ethical_url }}" placeholder="https://" required>
    </div>
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Description <span class="text-danger">*</span></label>
        <textarea name="meta[ethical_description]" class="form-control text-editor" rows="4" required>{{ $ethical_description }}</textarea>
    </div>
</div>

<div class="row">
    <div class="col-md-12"><hr><h4 class="text-primary">Risk & Control Section</h4></div>
    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[risk_control_title]" value="{{ $risk_control_title }}" required>
    </div>
    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Subtitle <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[risk_control_subtitle]" value="{{ $risk_control_subtitle }}" required>
    </div>
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Description <span class="text-danger">*</span></label>
        <textarea name="meta[risk_control_description]" class="form-control text-editor" rows="4" required>{{ $risk_control_description }}</textarea>
    </div>
    <div class="risk-control-items-target col-md-12">
        @if(isset($risk_control_items['itration']) && is_array($risk_control_items['itration']))
            @foreach($risk_control_items['itration'] as $index => $itration)
                <div class="row remove-parent">
                    <div class="col-md-11">
                        <div class="row">
                            <div class="col-md-12"><input value="{{ $index }}" name="meta[risk_control_items][itration][]" type="hidden" required></div>
                            <div class="col-md-6 form-group mb-2">
                                {{-- <label class="form-label">Icon <span class="text-danger">*</span></label> --}}
                                <div class="form-group mb-2">
                                    <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                        <div class="input-group-prepend"><div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div></div>
                                        <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                        <input type="hidden" name="meta[risk_control_items][icon][]" class="selected-files" value="{{ $risk_control_items['icon'][$index] ?? '' }}" required>
                                    </div>
                                    <div class="file-preview box sm"></div>
                                </div>
                            </div>
                            <div class="col-md-6 form-group mb-2">
                                {{-- <label class="form-label">Title <span class="text-danger">*</span></label> --}}
                                <input type="text" name="meta[risk_control_items][title][]" class="form-control" value="{{ $risk_control_items['title'][$index] ?? '' }}" required>
                            </div>
                            <div class="col-md-12 form-group mb-2">
                                {{-- <label class="form-label">Description <span class="text-danger">*</span></label> --}}
                                <textarea name="meta[risk_control_items][description][]" class="form-control" rows="3" required>{{ $risk_control_items['description'][$index] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1 btn-dynamic-fields">
                        <button type="button" class="btn btn-icon btn-circle btn-soft-danger" data-toggle="remove-parent" data-parent=".remove-parent"><i class="ti ti-x"></i></button>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
    <button type="button" class="mt-1 btn btn-soft-success btn-icon w-100" data-toggle="add-more" data-limit="15" data-content='
            <div class="row remove-parent">
                <div class="col-md-11">
                    <div class="row">
                        <div class="col-md-12"><input value="data" name="meta[risk_control_items][itration][]" type="hidden" required></div>
                        <div class="col-md-6 form-group mb-2">
                            {{-- <label class="form-label">Icon <span class="text-danger">*</span></label> --}}
                            <div class="form-group mb-2">
                                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                    <div class="input-group-prepend"><div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div></div>
                                    <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                    <input type="hidden" name="meta[risk_control_items][icon][]" class="selected-files" required>
                                </div>
                                <div class="file-preview box sm"></div>
                            </div>
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            {{-- <label class="form-label">Title <span class="text-danger">*</span></label> --}}
                            <input type="text" name="meta[risk_control_items][title][]" class="form-control" placeholder="Enter title *" required>
                        </div>
                        <div class="col-md-12 form-group mb-2">
                            {{-- <label class="form-label">Description <span class="text-danger">*</span></label> --}}
                            <textarea name="meta[risk_control_items][description][]" class="form-control" rows="3" placeholder="Enter description *" required></textarea>
                        </div>
                    </div>
                </div>
                <div class="col-md-1 btn-dynamic-fields">
                    <button type="button" class="btn btn-icon btn-circle btn-soft-danger" data-toggle="remove-parent" data-parent=".remove-parent"><i class="ti ti-x"></i></button>
                </div>
            </div>
        ' data-target=".risk-control-items-target">
        <i class="ti ti-plus"></i><span class="ml-2">Add More</span>
    </button>
</div>

<div class="row">
    <div class="col-md-12"><hr><h4 class="text-primary">Global Standard Section</h4></div>
    <div class="col-md-4 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[global_standard_title]" value="{{ $global_standard_title }}" required>
    </div>
    <div class="col-md-4 form-group mb-2">
        <label class="form-label">Subtitle <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[global_standard_subtitle]" value="{{ $global_standard_subtitle }}" required>
    </div>
    <div class="col-md-4 form-group mb-2">
        <label class="form-label">Image <span class="text-danger">*</span></label>
        <div class="form-group mb-2">
            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                <div class="input-group-prepend"><div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div></div>
                <div class="form-control file-amount">{{ __('Choose File') }}</div>
                <input value="{{ $global_standard_image }}" type="hidden" name="meta[global_standard_image]" class="selected-files" required>
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div>
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Description <span class="text-danger">*</span></label>
        <textarea name="meta[global_standard_description]" class="form-control text-editor" rows="4" required>{{ $global_standard_description }}</textarea>
    </div>
    <div class="global-standard-items-target col-md-12">
        @if(isset($global_standard_items['itration']) && is_array($global_standard_items['itration']))
            @foreach($global_standard_items['itration'] as $index => $itration)
                <div class="row remove-parent">
                    <div class="col-md-11">
                        <div class="row">
                            <div class="col-md-12"><input value="{{ $index }}" name="meta[global_standard_items][itration][]" type="hidden" required></div>
                            <div class="col-md-6 form-group mb-2">
                                {{-- <label class="form-label">Icon <span class="text-danger">*</span></label> --}}
                                <div class="form-group mb-2">
                                    <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                        <div class="input-group-prepend"><div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div></div>
                                        <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                        <input type="hidden" name="meta[global_standard_items][icon][]" class="selected-files" value="{{ $global_standard_items['icon'][$index] ?? '' }}" required>
                                    </div>
                                    <div class="file-preview box sm"></div>
                                </div>
                            </div>
                            <div class="col-md-6 form-group mb-2">
                                {{-- <label class="form-label">Title <span class="text-danger">*</span></label> --}}
                                <input type="text" name="meta[global_standard_items][title][]" class="form-control" value="{{ $global_standard_items['title'][$index] ?? '' }}" required>
                            </div>
                            <div class="col-md-12 form-group mb-2">
                                <label class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea name="meta[global_standard_items][description][]" class="form-control" rows="3" required>{{ $global_standard_items['description'][$index] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1 btn-dynamic-fields">
                        <button type="button" class="btn btn-icon btn-circle btn-soft-danger" data-toggle="remove-parent" data-parent=".remove-parent"><i class="ti ti-x"></i></button>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
    <button type="button" class="mt-1 btn btn-soft-success btn-icon w-100" data-toggle="add-more" data-limit="15" data-content='
            <div class="row remove-parent">
                <div class="col-md-11">
                    <div class="row">
                        <div class="col-md-12"><input value="data" name="meta[global_standard_items][itration][]" type="hidden" required></div>
                        <div class="col-md-6 form-group mb-2">
                            {{-- <label class="form-label">Icon <span class="text-danger">*</span></label> --}}
                            <div class="form-group mb-2">
                                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                    <div class="input-group-prepend"><div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div></div>
                                    <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                    <input type="hidden" name="meta[global_standard_items][icon][]" class="selected-files" required>
                                </div>
                                <div class="file-preview box sm"></div>
                            </div>
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            {{-- <label class="form-label">Title <span class="text-danger">*</span></label> --}}
                            <input type="text" name="meta[global_standard_items][title][]" class="form-control" placeholder="Enter title *" required>
                        </div>
                        <div class="col-md-12 form-group mb-2">
                            {{-- <label class="form-label">Description <span class="text-danger">*</span></label> --}}
                            <textarea name="meta[global_standard_items][description][]" class="form-control" rows="3" placeholder="Enter description *" required></textarea>
                        </div>
                    </div>
                </div>
                <div class="col-md-1 btn-dynamic-fields">
                    <button type="button" class="btn btn-icon btn-circle btn-soft-danger" data-toggle="remove-parent" data-parent=".remove-parent"><i class="ti ti-x"></i></button>
                </div>
            </div>
        ' data-target=".global-standard-items-target">
        <i class="ti ti-plus"></i><span class="ml-2">Add More</span>
    </button>
</div>

<div class="row">
    <div class="col-md-12"><hr><h4 class="text-primary">Digital Trust Section</h4></div>
    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[digital_trust_title]" value="{{ $digital_trust_title }}" required>
    </div>
    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Subtitle <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[digital_trust_subtitle]" value="{{ $digital_trust_subtitle }}" required>
    </div>
    {{-- <div class="col-md-4 form-group mb-2">
        <label class="form-label">Image <span class="text-danger">*</span></label>
        <div class="form-group mb-2">
            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                <div class="input-group-prepend"><div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div></div>
                <div class="form-control file-amount">{{ __('Choose File') }}</div>
                <input value="{{ $digital_trust_image }}" type="hidden" name="meta[digital_trust_image]" class="selected-files" required>
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div> --}}
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Description <span class="text-danger">*</span></label>
        <textarea name="meta[digital_trust_description]" class="form-control text-editor" rows="4" required>{{ $digital_trust_description }}</textarea>
    </div>

    <div class="col-md-12"><p class="text-muted mb-2">Add more (Type 1)</p></div>
    <div class="digital-trust-items-target col-md-12">
        @if(isset($digital_trust_items['itration']) && is_array($digital_trust_items['itration']))
            @foreach($digital_trust_items['itration'] as $index => $itration)
                <div class="row remove-parent">
                    <div class="col-md-11">
                        <div class="row">
                            <div class="col-md-12"><input value="{{ $index }}" name="meta[digital_trust_items][itration][]" type="hidden" required></div>
                            <div class="col-md-6 form-group mb-2">
                                {{-- <label class="form-label">Icon <span class="text-danger">*</span></label> --}}
                                <div class="form-group mb-2">
                                    <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                        <div class="input-group-prepend"><div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div></div>
                                        <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                        <input type="hidden" name="meta[digital_trust_items][icon][]" class="selected-files" value="{{ $digital_trust_items['icon'][$index] ?? '' }}" required>
                                    </div>
                                    <div class="file-preview box sm"></div>
                                </div>
                            </div>
                            <div class="col-md-6 form-group mb-2">
                                {{-- <label class="form-label">Title <span class="text-danger">*</span></label> --}}
                                <input type="text" name="meta[digital_trust_items][title][]" class="form-control" value="{{ $digital_trust_items['title'][$index] ?? '' }}" required>
                            </div>
                            <div class="col-md-12 form-group mb-2">
                                <label class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea name="meta[digital_trust_items][description][]" class="form-control" rows="3" required>{{ $digital_trust_items['description'][$index] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1 btn-dynamic-fields">
                        <button type="button" class="btn btn-icon btn-circle btn-soft-danger" data-toggle="remove-parent" data-parent=".remove-parent"><i class="ti ti-x"></i></button>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
    <button type="button" class="mt-1 btn btn-soft-success btn-icon w-100" data-toggle="add-more" data-limit="15" data-content='
            <div class="row remove-parent">
                <div class="col-md-11">
                    <div class="row">
                        <div class="col-md-12"><input value="data" name="meta[digital_trust_items][itration][]" type="hidden" required></div>
                        <div class="col-md-6 form-group mb-2">
                            {{-- <label class="form-label">Icon <span class="text-danger">*</span></label> --}}
                            <div class="form-group mb-2">
                                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                    <div class="input-group-prepend"><div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div></div>
                                    <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                    <input type="hidden" name="meta[digital_trust_items][icon][]" class="selected-files" required>
                                </div>
                                <div class="file-preview box sm"></div>
                            </div>
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            {{-- <label class="form-label">Title <span class="text-danger">*</span></label> --}}
                            <input type="text" name="meta[digital_trust_items][title][]" class="form-control" placeholder="Enter title *" required>
                        </div>
                        <div class="col-md-12 form-group mb-2">
                            {{-- <label class="form-label">Description <span class="text-danger">*</span></label> --}}
                            <textarea name="meta[digital_trust_items][description][]" class="form-control" rows="3" placeholder="Enter description *" required></textarea>
                        </div>
                    </div>
                </div>
                <div class="col-md-1 btn-dynamic-fields">
                    <button type="button" class="btn btn-icon btn-circle btn-soft-danger" data-toggle="remove-parent" data-parent=".remove-parent"><i class="ti ti-x"></i></button>
                </div>
            </div>
        ' data-target=".digital-trust-items-target">
        <i class="ti ti-plus"></i><span class="ml-2">Add More</span>
    </button>

    <div class="col-md-12 mt-2"><p class="text-muted mb-2">Add more (Type 2)</p></div>
    <div class="digital-trust-type2-target col-md-12">
        @if(isset($digital_trust_type2_items['itration']) && is_array($digital_trust_type2_items['itration']))
            @foreach($digital_trust_type2_items['itration'] as $index => $itration)
                <div class="row remove-parent">
                    <div class="col-md-11">
                        <div class="row">
                            <div class="col-md-12"><input value="{{ $index }}" name="meta[digital_trust_type2_items][itration][]" type="hidden" required></div>
                            <div class="col-md-12 form-group mb-2">
                                {{-- <label class="form-label">Title <span class="text-danger">*</span></label> --}}
                                <input type="text" name="meta[digital_trust_type2_items][title][]" class="form-control" value="{{ $digital_trust_type2_items['title'][$index] ?? '' }}" required>
                            </div>
                            <div class="col-md-12 form-group mb-2">
                                {{-- <label class="form-label">Description <span class="text-danger">*</span></label> --}}
                                <textarea name="meta[digital_trust_type2_items][description][]" class="form-control" rows="3" required>{{ $digital_trust_type2_items['description'][$index] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1 btn-dynamic-fields">
                        <button type="button" class="btn btn-icon btn-circle btn-soft-danger" data-toggle="remove-parent" data-parent=".remove-parent"><i class="ti ti-x"></i></button>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
    <button type="button" class="mt-1 btn btn-soft-success btn-icon w-100" data-toggle="add-more" data-limit="15" data-content='
            <div class="row remove-parent">
                <div class="col-md-11">
                    <div class="row">
                        <div class="col-md-12"><input value="data" name="meta[digital_trust_type2_items][itration][]" type="hidden" required></div>
                        <div class="col-md-12 form-group mb-2">
                            {{-- <label class="form-label">Title <span class="text-danger">*</span></label> --}}
                            <input type="text" name="meta[digital_trust_type2_items][title][]" class="form-control" placeholder="Enter title *" required>
                        </div>
                        <div class="col-md-12 form-group mb-2">
                            {{-- <label class="form-label">Description <span class="text-danger">*</span></label> --}}
                            <textarea name="meta[digital_trust_type2_items][description][]" class="form-control" rows="3" placeholder="Enter description *" required></textarea>
                        </div>
                    </div>
                </div>
                <div class="col-md-1 btn-dynamic-fields">
                    <button type="button" class="btn btn-icon btn-circle btn-soft-danger" data-toggle="remove-parent" data-parent=".remove-parent"><i class="ti ti-x"></i></button>
                </div>
            </div>
        ' data-target=".digital-trust-type2-target">
        <i class="ti ti-plus"></i><span class="ml-2">Add More</span>
    </button>
</div>

<div class="row">
    <div class="col-md-12"><hr><h4 class="text-primary">Speak Up Section</h4></div>
    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[speak_up_title]" value="{{ $speak_up_title }}" required>
    </div>
    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Subtitle <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[speak_up_subtitle]" value="{{ $speak_up_subtitle }}" required>
    </div>
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Description <span class="text-danger">*</span></label>
        <textarea name="meta[speak_up_description]" class="form-control text-editor" rows="4" required>{{ $speak_up_description }}</textarea>
    </div>
    <div class="speak-up-items-target col-md-12">
        @if(isset($speak_up_items['itration']) && is_array($speak_up_items['itration']))
            @foreach($speak_up_items['itration'] as $index => $itration)
                <div class="row remove-parent">
                    <div class="col-md-11">
                        <div class="row">
                            <div class="col-md-12"><input value="{{ $index }}" name="meta[speak_up_items][itration][]" type="hidden" required></div>
                            <div class="col-md-6 form-group mb-2">
                                {{-- <label class="form-label">Icon <span class="text-danger">*</span></label> --}}
                                <div class="form-group mb-2">
                                    <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                        <div class="input-group-prepend"><div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div></div>
                                        <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                        <input type="hidden" name="meta[speak_up_items][icon][]" class="selected-files" value="{{ $speak_up_items['icon'][$index] ?? '' }}" required>
                                    </div>
                                    <div class="file-preview box sm"></div>
                                </div>
                            </div>
                            <div class="col-md-4 form-group mb-2">
                                {{-- <label class="form-label">Title <span class="text-danger">*</span></label> --}}
                                <input type="text" name="meta[speak_up_items][title][]" class="form-control" value="{{ $speak_up_items['title'][$index] ?? '' }}" required>
                            </div>
                            <div class="col-md-12 form-group mb-2">
                                {{-- <label class="form-label">Description <span class="text-danger">*</span></label> --}}
                                <textarea name="meta[speak_up_items][description][]" class="form-control" rows="3" required>{{ $speak_up_items['description'][$index] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1 btn-dynamic-fields">
                        <button type="button" class="btn btn-icon btn-circle btn-soft-danger" data-toggle="remove-parent" data-parent=".remove-parent"><i class="ti ti-x"></i></button>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
    <button type="button" class="mt-1 btn btn-soft-success btn-icon w-100" data-toggle="add-more" data-limit="15" data-content='
            <div class="row remove-parent">
                <div class="col-md-11">
                    <div class="row">
                        <div class="col-md-12"><input value="data" name="meta[speak_up_items][itration][]" type="hidden" required></div>
                        <div class="col-md-6 form-group mb-2">
                            {{-- <label class="form-label">Icon <span class="text-danger">*</span></label> --}}
                            <div class="form-group mb-2">
                                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                    <div class="input-group-prepend"><div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div></div>
                                    <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                    <input type="hidden" name="meta[speak_up_items][icon][]" class="selected-files" required>
                                </div>
                                <div class="file-preview box sm"></div>
                            </div>
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            {{-- <label class="form-label">Title <span class="text-danger">*</span></label> --}}
                            <input type="text" name="meta[speak_up_items][title][]" class="form-control" placeholder="Enter title" required>
                        </div>
                        <div class="col-md-12 form-group mb-2">
                            {{-- <label class="form-label">Description <span class="text-danger">*</span></label> --}}
                            <textarea name="meta[speak_up_items][description][]" class="form-control" rows="3" placeholder="Enter description" required></textarea>
                        </div>
                    </div>
                </div>
                <div class="col-md-1 btn-dynamic-fields">
                    <button type="button" class="btn btn-icon btn-circle btn-soft-danger" data-toggle="remove-parent" data-parent=".remove-parent"><i class="ti ti-x"></i></button>
                </div>
            </div>
        ' data-target=".speak-up-items-target">
        <i class="ti ti-plus"></i><span class="ml-2">Add More</span>
    </button>
</div>
