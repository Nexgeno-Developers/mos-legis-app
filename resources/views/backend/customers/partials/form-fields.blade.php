@php
    $d = fn ($key) => $details[$key] ?? '';
@endphp

<div class="col-sm-12">
    <h6 class="text-primary text-uppercase mb-2">{{ __('labels.personal_information') }}</h6>
</div>

<div class="col-sm-4">
    <label class="form-label">{{ __('labels.profile_photo') }}</label>
    <div class="form-group mb-2">
        <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
            <div class="input-group-prepend">
                <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
            </div>
            <div class="form-control file-amount">{{ __('Choose File') }}</div>
            <input type="hidden" name="details[profile_photo]" class="selected-files" value="{{ $d('profile_photo') }}">
        </div>
        <div class="file-preview box sm"></div>
    </div>
</div>

<div class="col-sm-2">
    <div class="form-group mb-2">
        <label class="form-label">{{ __('labels.name') }} <span class="text-danger">*</span></label>
        <input name="name" type="text" class="form-control" minlength="3" maxlength="200" value="{{ $user?->name ?? '' }}" required>
    </div>
</div>

<div class="col-sm-2">
    <div class="form-group mb-2">
        <label class="form-label">{{ __('labels.email') }} <span class="text-danger">*</span></label>
        <input name="email" type="email" class="form-control" value="{{ $user?->email ?? '' }}" required>
    </div>
</div>

<div class="col-sm-2">
    <div class="form-group mb-2">
        <label class="form-label">{{ __('labels.phone') }} <span class="text-danger">*</span></label>
        <input name="phone" type="tel" class="form-control" minlength="10" maxlength="10" pattern="[0-9]{10}" inputmode="numeric" value="{{ $user?->phone ?? '' }}" required>
    </div>
</div>

<div class="col-sm-2">
    <div class="form-group mb-2">
        <label class="form-label">{{ __('labels.status') }} <span class="text-danger">*</span></label>
        <select name="is_active" class="form-select select2" required>
            <option value="1" {{ ($user?->is_active ?? 1) ? 'selected' : '' }}>Active</option>
            <option value="0" {{ isset($user) && !$user?->is_active ? 'selected' : '' }}>Inactive</option>
        </select>
    </div>
</div>

<div class="col-sm-12">
    <hr>
    <h6 class="text-primary text-uppercase mb-2">{{ __('labels.billing_address') }}</h6>
</div>

<div class="col-sm-12">
    <div class="form-group mb-2">
        <label class="form-label">{{ __('labels.street_address') }}</label>
        <textarea name="details[street_address]" class="form-control" rows="2">{{ $d('street_address') }}</textarea>
    </div>
</div>

<div class="col-sm-3">
    <div class="form-group mb-2">
        <label class="form-label">{{ __('labels.city') }}</label>
        <input name="details[city]" type="text" class="form-control" value="{{ $d('city') }}">
    </div>
</div>

<div class="col-sm-3">
    <div class="form-group mb-2">
        <label class="form-label">{{ __('labels.state') }}</label>
        <input name="details[state]" type="text" class="form-control" value="{{ $d('state') }}">
    </div>
</div>

<div class="col-sm-3">
    <div class="form-group mb-2">
        <label class="form-label">{{ __('labels.pin_code') }}</label>
        <input name="details[pin_code]" type="text" class="form-control" value="{{ $d('pin_code') }}">
    </div>
</div>

<div class="col-sm-3">
    <div class="form-group mb-2">
        <label class="form-label">{{ __('labels.country') }}</label>
        <input name="details[country]" type="text" class="form-control" value="{{ $d('country') }}">
    </div>
</div>

<div class="col-sm-12">
    <hr>
    <h6 class="text-primary text-uppercase mb-2">{{ __('labels.company_details') }}</h6>
</div>

<div class="col-sm-3">
    <label class="form-label">{{ __('labels.company_logo') }}</label>
    <div class="form-group mb-2">
        <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
            <div class="input-group-prepend">
                <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
            </div>
            <div class="form-control file-amount">{{ __('Choose File') }}</div>
            <input type="hidden" name="details[company_logo]" class="selected-files" value="{{ $d('company_logo') }}">
        </div>
        <div class="file-preview box sm"></div>
    </div>
</div>

<div class="col-sm-3">
    <div class="form-group mb-2">
        <label class="form-label">{{ __('labels.company_name') }}</label>
        <input name="details[company_name]" type="text" class="form-control" value="{{ $d('company_name') }}">
    </div>
</div>

<div class="col-sm-3">
    <div class="form-group mb-2">
        <label class="form-label">{{ __('labels.gstin') }}</label>
        <input name="details[gstin]" type="text" class="form-control" value="{{ $d('gstin') }}">
    </div>
</div>

<div class="col-sm-3">
    <div class="form-group mb-2">
        <label class="form-label">{{ __('labels.pan_number') }}</label>
        <input name="details[pan_number]" type="text" class="form-control" value="{{ $d('pan_number') }}">
    </div>
</div>
