@php
    $breadcrumb_image = $pageData->meta->where('meta_key', 'breadcrumb_image')->first()->meta_value ?? '';

    $short_summary_image = $pageData->meta->where('meta_key', 'short_summary_image')->first()->meta_value ?? '';
    $short_summary_description = $pageData->meta->where('meta_key', 'short_summary_description')->first()->meta_value ?? '';

    $hero_title = $pageData->meta->where('meta_key', 'hero_title')->first()->meta_value ?? '';
    $hero_image = $pageData->meta->where('meta_key', 'hero_image')->first()->meta_value ?? '';
    $hero_description = $pageData->meta->where('meta_key', 'hero_description')->first()->meta_value ?? '';

    $special_ability_title = $pageData->meta->where('meta_key', 'special_ability_title')->first()->meta_value ?? '';
    $special_ability_description = $pageData->meta->where('meta_key', 'special_ability_description')->first()->meta_value ?? '';
    $special_ability_card_1_description = $pageData->meta->where('meta_key', 'special_ability_card_1_description')->first()->meta_value ?? '';
    $special_ability_card_2_video_url = $pageData->meta->where('meta_key', 'special_ability_card_2_video_url')->first()->meta_value ?? '';
    $special_ability_card_3_description = $pageData->meta->where('meta_key', 'special_ability_card_3_description')->first()->meta_value ?? '';
    $special_ability_items = json_decode($pageData->meta->where('meta_key', 'special_ability_items')->first()->meta_value ?? '[]', true);
    $special_ability_items = is_array($special_ability_items) ? $special_ability_items : [];

    $special_ability_images = json_decode($pageData->meta->where('meta_key', 'special_ability_images')->first()->meta_value ?? '[]', true);
    $special_ability_images = is_array($special_ability_images) ? $special_ability_images : [];

    $lamira_love_title = $pageData->meta->where('meta_key', 'lamira_love_title')->first()->meta_value ?? '';
    $lamira_love_description = $pageData->meta->where('meta_key', 'lamira_love_description')->first()->meta_value ?? '';
    $lamira_love_items = json_decode($pageData->meta->where('meta_key', 'lamira_love_items')->first()->meta_value ?? '[]', true);
    $lamira_love_items = is_array($lamira_love_items) ? $lamira_love_items : [];

    $shared_guide_title = $pageData->meta->where('meta_key', 'shared_guide_title')->first()->meta_value ?? '';
    $shared_guide_image = $pageData->meta->where('meta_key', 'shared_guide_image')->first()->meta_value ?? '';
    $shared_guide_description = $pageData->meta->where('meta_key', 'shared_guide_description')->first()->meta_value ?? '';

    $social_world_title = $pageData->meta->where('meta_key', 'social_world_title')->first()->meta_value ?? '';
    $social_world_images = $pageData->meta->where('meta_key', 'social_world_images')->first()->meta_value ?? '';
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
    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[hero_title]" value="{{ $hero_title }}" required>
    </div>
    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Image <span class="text-danger">*</span></label>
        <div class="form-group mb-2">
            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                </div>
                <div class="form-control file-amount">{{ __('Choose File') }}</div>
                <input value="{{ $hero_image }}" type="hidden" name="meta[hero_image]" class="selected-files" required>
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div>
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Description <span class="text-danger">*</span></label>
        <textarea name="meta[hero_description]" class="form-control text-editor" rows="4" required>{{ $hero_description }}</textarea>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Special Ability Section</h4>
    </div>
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[special_ability_title]" value="{{ $special_ability_title }}" required>
    </div>
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Description <span class="text-danger">*</span></label>
        <textarea name="meta[special_ability_description]" class="form-control" rows="4" required>{{ $special_ability_description }}</textarea>
    </div>

    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Card 1 Description <span class="text-danger">*</span></label>
        <textarea name="meta[special_ability_card_1_description]" class="form-control text-editor" rows="4" required>{{ $special_ability_card_1_description }}</textarea>
    </div>
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Card 2 Video Link <span class="text-danger">*</span></label>
        <input type="url" class="form-control" name="meta[special_ability_card_2_video_url]" value="{{ $special_ability_card_2_video_url }}" placeholder="Enter video URL *" required>
    </div>
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Card 3 Description <span class="text-danger">*</span></label>
        <textarea name="meta[special_ability_card_3_description]" class="form-control text-editor" rows="4" required>{{ $special_ability_card_3_description }}</textarea>
    </div>

    <div class="special-ability-items-target col-md-12 d-none">
        @if(isset($special_ability_items['itration']) && is_array($special_ability_items['itration']))
            @foreach($special_ability_items['itration'] as $index => $itration)
                <div class="row remove-parent">
                    <div class="col-md-11">
                        <div class="row">
                            <div class="col-md-12">
                                <input value="{{ $index }}" name="meta[special_ability_items][itration][]" type="hidden">
                            </div>
                            <div class="col-md-4 form-group mb-2">
                                {{-- <label class="form-label">Title <span class="text-danger">*</span></label> --}}
                                <input type="text" name="meta[special_ability_items][title][]" class="form-control" value="{{ $special_ability_items['title'][$index] ?? '' }}">
                            </div>
                            <div class="col-md-4 form-group mb-2">
                                {{-- <label class="form-label">Video <span class="text-danger">*</span></label> --}}
                                <input type="url" name="meta[special_ability_items][video_url][]" class="form-control" value="{{ $special_ability_items['video_url'][$index] ?? '' }}" placeholder="Enter video URL *">
                            </div>
                            <div class="col-md-4 form-group mb-2">
                                {{-- <label class="form-label">Description <span class="text-danger">*</span></label> --}}
                                <textarea name="meta[special_ability_items][description][]" class="form-control" rows="3">{{ $special_ability_items['description'][$index] ?? '' }}</textarea>
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
        class="mt-1 btn btn-soft-success btn-icon w-100 d-none"
        data-toggle="add-more"
        data-limit="3"
        data-content='
            <div class="row remove-parent">
                <div class="col-md-11">
                    <div class="row">
                        <div class="col-md-12">
                            <input value="data" name="meta[special_ability_items][itration][]" type="hidden" required>
                        </div>
                        <div class="col-md-4 form-group mb-2">
                            {{-- <label class="form-label">Title <span class="text-danger">*</span></label> --}}
                            <input type="text" name="meta[special_ability_items][title][]" class="form-control" placeholder="Enter title *" required>
                        </div>
                        <div class="col-md-4 form-group mb-2">
                            {{-- <label class="form-label">Video <span class="text-danger">*</span></label> --}}
                            <input type="url" name="meta[special_ability_items][video_url][]" class="form-control" placeholder="Enter video URL *" required>
                        </div>
                        <div class="col-md-4 form-group mb-2">
                            {{-- <label class="form-label">Description <span class="text-danger">*</span></label> --}}
                            <textarea name="meta[special_ability_items][description][]" class="form-control" rows="3" placeholder="Enter description *" required></textarea>
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
        data-target=".special-ability-items-target"
    >
        <i class="ti ti-plus"></i>
        <span class="ml-2">Add More</span>
    </button>

    <div class="special-ability-images-target col-md-12 d-none">
        @if(isset($special_ability_images['itration']) && is_array($special_ability_images['itration']))
            @foreach($special_ability_images['itration'] as $index => $itration)
                <div class="row remove-parent">
                    <div class="col-md-11">
                        <div class="row">
                            <div class="col-md-12">
                                <input value="{{ $index }}" name="meta[special_ability_images][itration][]" type="hidden" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Image <span class="text-danger">*</span></label>
                                <div class="form-group mb-2">
                                    <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                        </div>
                                        <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                        <input type="hidden" name="meta[special_ability_images][image][]" class="selected-files" value="{{ $special_ability_images['image'][$index] ?? '' }}">
                                    </div>
                                    <div class="file-preview box sm"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1 btn-dynamic-fields mb-1">
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
                            <input value="data" name="meta[special_ability_images][itration][]" type="hidden" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Image <span class="text-danger">*</span></label>
                            <div class="form-group mb-2">
                                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                    <input type="hidden" name="meta[special_ability_images][image][]" class="selected-files" required>
                                </div>
                                <div class="file-preview box sm"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-1 btn-dynamic-fields mb-1">
                    <button type="button" class="btn btn-icon btn-circle btn-soft-danger" data-toggle="remove-parent" data-parent=".remove-parent">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
            </div>
        '
        data-target=".special-ability-images-target"
    >
        <i class="ti ti-plus"></i>
        <span class="ml-2">Add More Images</span>
    </button>

    
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Lamira Love Section</h4>
    </div>
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[lamira_love_title]" value="{{ $lamira_love_title }}" required>
    </div>
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Description <span class="text-danger">*</span></label>
        <textarea name="meta[lamira_love_description]" class="form-control" rows="4" required>{{ $lamira_love_description }}</textarea>
    </div>

    <div class="lamira-love-items-target col-md-12">
        @if(isset($lamira_love_items['itration']) && is_array($lamira_love_items['itration']))
            @foreach($lamira_love_items['itration'] as $index => $itration)
                <div class="row remove-parent">
                    <div class="col-md-11">
                        <div class="row">
                            <div class="col-md-12">
                                <input value="{{ $index }}" name="meta[lamira_love_items][itration][]" type="hidden" required>
                            </div>
                            <div class="col-md-4 form-group mb-2">
                                {{-- <label class="form-label">Title <span class="text-danger">*</span></label> --}}
                                <input type="text" name="meta[lamira_love_items][title][]" class="form-control" value="{{ $lamira_love_items['title'][$index] ?? '' }}" required>
                            </div>
                            <div class="col-md-4">
                                {{-- <label class="form-label">Image <span class="text-danger">*</span></label> --}}
                                <div class="form-group mb-2">
                                    <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                        </div>
                                        <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                        <input type="hidden" name="meta[lamira_love_items][image][]" class="selected-files" value="{{ $lamira_love_items['image'][$index] ?? '' }}" required>
                                    </div>
                                    <div class="file-preview box sm"></div>
                                </div>
                            </div>
                            <div class="col-md-4 form-group mb-2">
                                {{-- <label class="form-label">Description <span class="text-danger">*</span></label> --}}
                                <textarea name="meta[lamira_love_items][description][]" class="form-control" rows="3" required>{{ $lamira_love_items['description'][$index] ?? '' }}</textarea>
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
        data-limit="3"
        data-content='
            <div class="row remove-parent">
                <div class="col-md-11">
                    <div class="row">
                        <div class="col-md-12">
                            <input value="data" name="meta[lamira_love_items][itration][]" type="hidden" required>
                        </div>
                        <div class="col-md-4 form-group mb-2">
                            {{-- <label class="form-label">Title <span class="text-danger">*</span></label> --}}
                            <input type="text" name="meta[lamira_love_items][title][]" class="form-control" placeholder="Enter title" required>
                        </div>
                        <div class="col-md-4">
                            {{-- <label class="form-label">Image <span class="text-danger">*</span></label> --}}
                            <div class="form-group mb-2">
                                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                    <input type="hidden" name="meta[lamira_love_items][image][]" class="selected-files" required>
                                </div>
                                <div class="file-preview box sm"></div>
                            </div>
                        </div>
                        <div class="col-md-4 form-group mb-2">
                            {{-- <label class="form-label">Description <span class="text-danger">*</span></label> --}}
                            <textarea name="meta[lamira_love_items][description][]" class="form-control" rows="3" placeholder="Enter description *" required></textarea>
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
        data-target=".lamira-love-items-target"
    >
        <i class="ti ti-plus"></i>
        <span class="ml-2">Add More</span>
    </button>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Shared Guide For Future Section</h4>
    </div>
    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[shared_guide_title]" value="{{ $shared_guide_title }}" required>
    </div>
    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Image <span class="text-danger">*</span></label>
        <div class="form-group mb-2">
            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                </div>
                <div class="form-control file-amount">{{ __('Choose File') }}</div>
                <input value="{{ $shared_guide_image }}" type="hidden" name="meta[shared_guide_image]" class="selected-files" required>
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div>
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Description <span class="text-danger">*</span></label>
        <textarea name="meta[shared_guide_description]" class="form-control" rows="4" required>{{ $shared_guide_description }}</textarea>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Social World Section</h4>
    </div>
    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[social_world_title]" value="{{ $social_world_title }}" required>
    </div>
    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Image <span class="text-danger">*</span></label>
        <div class="form-group mb-2">
            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="true">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                </div>
                <div class="form-control file-amount">{{ __('Choose File') }}</div>
                <input value="{{ $social_world_images }}" type="hidden" name="meta[social_world_images]" class="selected-files" required>
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div>
</div>
