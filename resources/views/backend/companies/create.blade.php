@extends('backend.layouts.app')

@section('content')
<div class="page-title-head d-flex align-items-center gap-2">
    <div class="flex-grow-1">
        <h4 class="fs-16 text-uppercase fw-bold mb-0">{{$moduleName}} / Create</h4>
    </div>
    <div class="text-end">
        <ol class="breadcrumb m-0 py-0 fs-13">
            <li class="breadcrumb-item"><a href="{{ route('companies.index') }}">Back to {{$moduleName}} list</a></li>
        </ol>
    </div>
</div>

<form class="form" action="{{ route('companies.store') }}" method="POST">
    @include('backend.includes.alert-message')
    @csrf
    <div class="row">
        <!-- Company Details -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-uppercase bg-light p-2 mt-0 mb-3">Primary Information</h5>
                    <div class="mb-3 form-group">
                        <label for="company-name" class="form-label">Company Name <span class="text-danger">*</span></label>
                        <input type="text" id="company-name" name="name" value="{{ old('name') }}" class="form-control" placeholder="e.g : Sample Company" required>
                    </div>
                    <div class="mb-2 form-group clearfix">
                        <label for="company-breadcrumb" class="form-label">{{ __('Breadcrumb') }} <span class="text-danger">*</span></label>
                        <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                            <div class="input-group-prepend">
                                <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                            </div>
                            <div class="form-control file-amount">{{ __('Choose File') }}</div>
                            <input type="hidden" id="company-breadcrumb" name="meta[breadcrumb]" value="{{ old('meta.breadcrumb') }}" class="selected-files" required>
                        </div>
                        <div class="file-preview box sm"></div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="mb-2 form-group clearfix">
                        <label for="company-logo" class="form-label">{{ __('Logo') }} <span class="text-danger">*</span></label>
                        <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                            <div class="input-group-prepend">
                                <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                            </div>
                            <div class="form-control file-amount">{{ __('Choose File') }}</div>
                            <input type="hidden" id="company-logo" name="logo" value="{{ old('logo') }}" class="selected-files" required>
                        </div>
                        <div class="file-preview box sm"></div>
                    </div>
                    <div class="clearfix"></div>

                    <div class="mb-2 form-group clearfix">
                        <label for="company-footer-logo" class="form-label">{{ __('Footer Logo Image') }}</label>
                        <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                            <div class="input-group-prepend">
                                <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                            </div>
                            <div class="form-control file-amount">{{ __('Choose File') }}</div>
                            <input type="hidden" id="company-footer-logo" name="footer_logo_image" value="{{ old('footer_logo_image') }}" class="selected-files">
                        </div>
                        <div class="file-preview box sm"></div>
                    </div>
                    <div class="clearfix"></div>

                    <div class="mb-3 form-group">
                        <label for="company-short-description" class="form-label">{{ __('Short Description') }}</label>
                        <textarea id="company-short-description" name="short_description" class="form-control" rows="3">{{ old('short_description') }}</textarea>
                    </div>

                    <div class="mb-3 mt-1 form-group">
                        <label for="company-website" class="form-label">Website <span class="text-danger">*</span></label>
                        <input type="url" id="company-website" name="website" value="{{ old('website') }}" class="form-control" placeholder="" required>
                    </div>

                    <div class="mb-3 form-group">
                        <label for="company-google-map" class="form-label">{{ __('Map') }}</label>
                        <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                            <div class="input-group-prepend">
                                <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                            </div>
                            <div class="form-control file-amount">{{ __('Choose File') }}</div>
                            <input type="hidden" id="company-google-map" name="google_map" value="{{ old('google_map') }}" class="selected-files">
                        </div>
                        <div class="file-preview box sm"></div>
                    </div>

                    <div class="mb-3 form-group">
                        <label for="company-address" class="form-label">Address <span class="text-danger">*</span></label>
                        <input type="text" id="company-address" name="address" value="{{ old('address') }}" class="form-control" placeholder="e.g : 123 Main St, City, Country" required>
                    </div>

                    <div class="mb-3 form-group">
                        <label for="company-phone" class="form-label">Phone <span class="text-danger">*</span></label>
                        <input type="text" id="company-phone" name="phone" value="{{ old('phone') }}" class="form-control" placeholder="" required>
                    </div>

                    <div class="mb-3 form-group">
                        <label for="company-email" class="form-label">Enquiry Email <span class="text-danger">*</span></label>
                        <input type="email" id="company-email" name="email" value="{{ old('email') }}" class="form-control" placeholder="" required>
                    </div>

                    <div class="mb-3 form-group">
                        <label for="company-sales-partnership-email" class="form-label">Sales &amp; Partnership Email</label>
                        <input type="email" id="company-sales-partnership-email" name="meta[sales_partner_email]" value="{{ old('meta.sales_partner_email') }}" class="form-control" placeholder="">
                    </div>

                    <div class="mb-3 form-group">
                        <label for="company-technical-support-email" class="form-label">Technical Support Email</label>
                        <input type="email" id="company-technical-support-email" name="meta[technical_support_email]" value="{{ old('meta.technical_support_email') }}" class="form-control" placeholder="">
                    </div>

                    <div class="mb-3 form-group">
                        <label for="company-careers-email" class="form-label">Careers Email</label>
                        <input type="email" id="company-careers-email" name="meta[careers_email]" value="{{ old('meta.careers_email') }}" class="form-control" placeholder="">
                    </div>
                </div>
            </div>

            <!-- Content Sections -->
            <div class="card">
                <div class="card-body">
                    <h5 class="text-uppercase bg-light p-2 mt-0 mb-3">Content Sections</h5>

                    <div class="row">
                        <div class="col-md-12">
                            <hr>
                            <h4 class="text-primary">Questions Section</h4>
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="meta[questions_title]" value="{{ old('meta.questions_title') }}">
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <label class="form-label">Sub Title</label>
                            <input type="text" class="form-control" name="meta[questions_subtitle]" value="{{ old('meta.questions_subtitle') }}">
                        </div>
                        <div class="col-md-12 form-group mb-2">
                            <label class="form-label">URL</label>
                            <input type="text" class="form-control" name="meta[questions_url]" value="{{ old('meta.questions_url') }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <hr>
                            <h4 class="text-primary">Subscribe Section</h4>
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="meta[subscribe_title]" value="{{ old('meta.subscribe_title') }}">
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <label class="form-label">Sub Title</label>
                            <input type="text" class="form-control" name="meta[subscribe_subtitle]" value="{{ old('meta.subscribe_subtitle') }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <hr>
                            <h4 class="text-primary">Guidance Section</h4>
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="meta[guidance_title]" value="{{ old('meta.guidance_title') }}">
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <label class="form-label">URL</label>
                            <input type="text" class="form-control" name="meta[guidance_url]" value="{{ old('meta.guidance_url') }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <hr>
                            <h4 class="text-primary">Industry Detail Guidance Section</h4>
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="meta[industry_detail_guidance_title]" value="{{ old('meta.industry_detail_guidance_title') }}">
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <label class="form-label">URL</label>
                            <input type="text" class="form-control" name="meta[industry_detail_guidance_url]" value="{{ old('meta.industry_detail_guidance_url') }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <hr>
                            <h4 class="text-primary">Contact Section</h4>
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="meta[contact_title]" value="{{ old('meta.contact_title') }}">
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <label class="form-label">Sub Title</label>
                            <input type="text" class="form-control" name="meta[contact_subtitle]" value="{{ old('meta.contact_subtitle') }}">
                        </div>
                        <div class="col-md-12 form-group mb-2">
                            <label class="form-label">Card Title</label>
                            <input type="text" class="form-control" name="meta[contact_card_title]" value="{{ old('meta.contact_card_title') }}">
                        </div>
                        <div class="col-md-12 form-group mb-2">
                            <label class="form-label">Card Description</label>
                            <textarea name="meta[contact_card_description]" class="form-control text-editor" rows="3">{{ old('meta.contact_card_description') }}</textarea>
                        </div>
                        <div class="col-md-12 form-group mb-2">
                            <label class="form-label">Card URL</label>
                            <input type="text" class="form-control" name="meta[contact_card_url]" value="{{ old('meta.contact_card_url') }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <hr>
                            <h4 class="text-primary">Technical Experts Section</h4>
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="meta[technical_experts_title]" value="{{ old('meta.technical_experts_title') }}">
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <label class="form-label">Form Title</label>
                            <input type="text" class="form-control" name="meta[technical_experts_form_title]" value="{{ old('meta.technical_experts_form_title') }}">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Image</label>
                            <div class="form-group mb-2">
                                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                    <input type="hidden" name="meta[technical_experts_image]" value="{{ old('meta.technical_experts_image') }}" class="selected-files">
                                </div>
                                <div class="file-preview box sm"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Secondary Meta Data -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-uppercase mt-0 mb-3 bg-light p-2">Social Links</h5>
                    <div class="mb-3 form-group">
                        <label for="meta-instagram" class="form-label">Instagram URL</label>
                        <input type="url" class="form-control" id="meta-instagram" name="meta[instagram_url]" value="{{ old('meta.instagram_url') }}" placeholder="Enter Instagram URL">
                    </div>
                    <div class="mb-3 form-group">
                        <label for="meta-x" class="form-label">X URL</label>
                        <input type="url" class="form-control" id="meta-x" name="meta[x_url]" value="{{ old('meta.x_url') }}" placeholder="Enter X URL">
                    </div>
                    <div class="mb-3 form-group">
                        <label for="meta-linkedin" class="form-label">LinkedIn URL</label>
                        <input type="url" class="form-control" id="meta-linkedin" name="meta[linkedin_url]" value="{{ old('meta.linkedin_url') }}" placeholder="Enter LinkedIn URL">
                    </div>
                    <div class="mb-3 form-group">
                        <label for="meta-facebook" class="form-label">Facebook URL</label>
                        <input type="url" class="form-control" id="meta-facebook" name="meta[facebook_url]" value="{{ old('meta.facebook_url') }}" placeholder="Enter Facebook URL">
                    </div>
                    <div class="mb-3 form-group">
                        <label for="meta-youtube" class="form-label">YouTube URL</label>
                        <input type="url" class="form-control" id="meta-youtube" name="meta[youtube_url]" value="{{ old('meta.youtube_url') }}" placeholder="Enter YouTube URL">
                    </div>
                    <div class="mb-3 form-group">
                        <label for="meta-tiktok" class="form-label">TikTok URL</label>
                        <input type="url" class="form-control" id="meta-tiktok" name="meta[tiktok_url]" value="{{ old('meta.tiktok_url') }}" placeholder="Enter TikTok URL">
                    </div>
                    <div class="mb-3 form-group">
                        <label for="meta-vimeo" class="form-label">Vimeo URL</label>
                        <input type="url" class="form-control" id="meta-vimeo" name="meta[vimeo_url]" value="{{ old('meta.vimeo_url') }}" placeholder="Enter Vimeo URL">
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="text-end">
                <button type="submit" class="btn btn-primary w-100">Create</button>
            </div>
        </div>
    </div>
</form>

<script defer>
    initValidate('.form');
</script>
@endsection
