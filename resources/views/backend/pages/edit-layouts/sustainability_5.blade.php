@php
    $breadcrumb_image = $pageData->meta->where('meta_key', 'breadcrumb_image')->first()->meta_value ?? '';
    $breadcrumb_description = $pageData->meta->where('meta_key', 'breadcrumb_description')->first()->meta_value ?? '';

    $short_summary_image = $pageData->meta->where('meta_key', 'short_summary_image')->first()->meta_value ?? '';
    $short_summary_description = $pageData->meta->where('meta_key', 'short_summary_description')->first()->meta_value ?? '';

    $membership_title = $pageData->meta->where('meta_key', 'membership_title')->first()->meta_value ?? '';
    $membership_map = $pageData->meta->where('meta_key', 'membership_map')->first()->meta_value ?? '';
    $membership_block_1_icon = $pageData->meta->where('meta_key', 'membership_block_1_icon')->first()->meta_value ?? '';
    $membership_block_1_url = $pageData->meta->where('meta_key', 'membership_block_1_url')->first()->meta_value ?? '';
    $membership_block_1 = $pageData->meta->where('meta_key', 'membership_block_1')->first()->meta_value ?? '';
    $membership_block_2_icon = $pageData->meta->where('meta_key', 'membership_block_2_icon')->first()->meta_value ?? '';
    $membership_block_2_url = $pageData->meta->where('meta_key', 'membership_block_2_url')->first()->meta_value ?? '';
    $membership_block_2 = $pageData->meta->where('meta_key', 'membership_block_2')->first()->meta_value ?? '';
    $membership_block_3_icon = $pageData->meta->where('meta_key', 'membership_block_3_icon')->first()->meta_value ?? '';
    $membership_block_3_url = $pageData->meta->where('meta_key', 'membership_block_3_url')->first()->meta_value ?? '';
    $membership_block_3 = $pageData->meta->where('meta_key', 'membership_block_3')->first()->meta_value ?? '';

    $circular_future_title = $pageData->meta->where('meta_key', 'circular_future_title')->first()->meta_value ?? '';
    $circular_future_description = $pageData->meta->where('meta_key', 'circular_future_description')->first()->meta_value ?? '';

    $community_title = $pageData->meta->where('meta_key', 'community_title')->first()->meta_value ?? '';
    $community_image = $pageData->meta->where('meta_key', 'community_image')->first()->meta_value ?? '';
    $community_description = $pageData->meta->where('meta_key', 'community_description')->first()->meta_value ?? '';
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
        <h4 class="text-primary">Membership Section</h4>
    </div>
    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[membership_title]" value="{{ $membership_title }}" required>
    </div>
    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Map <span class="text-danger">*</span></label>
        <div class="form-group mb-2">
            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                </div>
                <div class="form-control file-amount">{{ __('Choose File') }}</div>
                <input value="{{ $membership_map }}" type="hidden" name="meta[membership_map]" class="selected-files" required>
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div>

    <div class="col-md-4">
        <label class="form-label">Block 1 Icon <span class="text-danger">*</span></label>
        <div class="form-group mb-2">
            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                </div>
                <div class="form-control file-amount">{{ __('Choose File') }}</div>
                <input value="{{ $membership_block_1_icon }}" type="hidden" name="meta[membership_block_1_icon]" class="selected-files" required>
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div>
    <div class="col-md-8 form-group mb-2">
        <label class="form-label">Block 1 Url <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[membership_block_1_url]" value="{{ $membership_block_1_url }}" required>
    </div>
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Block 1 <span class="text-danger">*</span></label>
        <textarea name="meta[membership_block_1]" class="form-control text-editor" rows="4" required>{{ $membership_block_1 }}</textarea>
    </div>

    <div class="col-md-4">
        <label class="form-label">Block 2 Icon <span class="text-danger">*</span></label>
        <div class="form-group mb-2">
            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                </div>
                <div class="form-control file-amount">{{ __('Choose File') }}</div>
                <input value="{{ $membership_block_2_icon }}" type="hidden" name="meta[membership_block_2_icon]" class="selected-files" required>
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div>
    <div class="col-md-8 form-group mb-2">
        <label class="form-label">Block 2 Url <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[membership_block_2_url]" value="{{ $membership_block_2_url }}" required>
    </div>
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Block 2 <span class="text-danger">*</span></label>
        <textarea name="meta[membership_block_2]" class="form-control text-editor" rows="4" required>{{ $membership_block_2 }}</textarea>
    </div>

    <div class="col-md-4">
        <label class="form-label">Block 3 Icon <span class="text-danger">*</span></label>
        <div class="form-group mb-2">
            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                </div>
                <div class="form-control file-amount">{{ __('Choose File') }}</div>
                <input value="{{ $membership_block_3_icon }}" type="hidden" name="meta[membership_block_3_icon]" class="selected-files" required>
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div>
    <div class="col-md-8 form-group mb-2">
        <label class="form-label">Block 3 Url <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[membership_block_3_url]" value="{{ $membership_block_3_url }}" required>
    </div>
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Block 3 <span class="text-danger">*</span></label>
        <textarea name="meta[membership_block_3]" class="form-control text-editor" rows="4" required>{{ $membership_block_3 }}</textarea>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Circular Future Section</h4>
    </div>
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[circular_future_title]" value="{{ $circular_future_title }}" required>
    </div>
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Description <span class="text-danger">*</span></label>
        <textarea name="meta[circular_future_description]" class="form-control text-editor" rows="4" required>{{ $circular_future_description }}</textarea>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Community Section</h4>
    </div>
    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="meta[community_title]" value="{{ $community_title }}" required>
    </div>
    <div class="col-md-6 form-group mb-2">
        <label class="form-label">Image <span class="text-danger">*</span></label>
        <div class="form-group mb-2">
            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                </div>
                <div class="form-control file-amount">{{ __('Choose File') }}</div>
                <input value="{{ $community_image }}" type="hidden" name="meta[community_image]" class="selected-files" required>
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div>
    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Description <span class="text-danger">*</span></label>
        <textarea name="meta[community_description]" class="form-control text-editor" rows="4" required>{{ $community_description }}</textarea>
    </div>
</div>
