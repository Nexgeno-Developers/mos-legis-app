@extends('backend.layouts.app')

@section('content')
<div class="page-title-head d-flex align-items-center gap-2">
    <div class="flex-grow-1">
        <h4 class="fs-16 text-uppercase fw-bold mb-0">{{$moduleName}}</h4>
    </div>
	<div class="text-end">
		<ol class="breadcrumb m-0 py-0 fs-13">
			<li class="breadcrumb-item"><a href="{{ route('companies.index') }}">Back to {{$moduleName}} list</a></li>
		</ol>
	</div>    
</div>

<form class="form" action="{{ route('companies.update', $pageData->id) }}" method="POST">
    @include('backend.includes.alert-message')
    @csrf
    @method('PUT')
    <div class="row">
        <!-- Company Details -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-uppercase bg-light p-2 mt-0 mb-3">Primary Information</h5>
                    <div class="mb-3 form-group">
                        <label for="company-name" class="form-label">Company Name <span class="text-danger">*</span></label>
                        <input type="text" id="company-name" name="name" value="{{ old('name', $pageData->name) }}" class="form-control" placeholder="e.g : Sample Company" required>
                    </div>
                    <div class="mb-2 form-group clearfix">
                        <label for="company-logo" class="form-label">{{ __('Breadcrumb') }} <span class="text-danger">*</span></label>
                        <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                            <div class="input-group-prepend">
                                <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                            </div>
                            <div class="form-control file-amount">{{ __('Choose File') }}</div>
                            <input type="hidden" id="company-breadcrumb" name="meta[breadcrumb]" value="{{ old('meta.breadcrumb', $pageData->meta->where('meta_key', 'breadcrumb')->first()->meta_value ?? '') }}" class="selected-files" required>
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
                            <input type="hidden" id="company-logo" name="logo" value="{{ $pageData->logo }}" class="selected-files" required>
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
                            <input
                                type="hidden"
                                id="company-footer-logo"
                                name="footer_logo_image"
                                value="{{ old('footer_logo_image', $pageData->footer_logo_image) }}"
                                class="selected-files"
                            >
                        </div>
                        <div class="file-preview box sm"></div>
                    </div>
                    <div class="clearfix"></div>

                    <div class="mb-3 form-group">
                        <label for="company-short-description" class="form-label">{{ __('Short Description') }}</label>
                        <textarea
                            id="company-short-description"
                            name="short_description"
                            class="form-control"
                            rows="3"
                        >{{ old('short_description', $pageData->short_description) }}</textarea>
                    </div>

                    <div class="mb-3 mt-1 form-group">
                        <label for="company-website" class="form-label">Website <span class="text-danger">*</span></label>
                        <input type="url" id="company-website" name="website" value="{{ $pageData->website }}" class="form-control" placeholder="" required>
                    </div>  
                    
                    <div class="mb-3 form-group">
                        <label for="company-google-map" class="form-label">{{ __('Map') }}</label>
                        <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                            <div class="input-group-prepend">
                                <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                            </div>
                            <div class="form-control file-amount">{{ __('Choose File') }}</div>
                            <input type="hidden"
                                   id="company-google-map"
                                   name="google_map"
                                   value="{{ $pageData->google_map }}"
                                   class="selected-files">
                        </div>
                        <div class="file-preview box sm"></div>
                    </div>

                    <div class="mb-3 form-group">
                        <label for="company-address" class="form-label">Address <span class="text-danger">*</span></label>
                        <input type="text" id="company-address" name="address" value="{{ old('address', $pageData->address) }}" class="form-control" placeholder="e.g : 123 Main St, City, Country" required>
                    </div>    
                    
                    <div class="mb-3 form-group">
                        <label for="company-phone" class="form-label">Phone <span class="text-danger">*</span></label>
                        <input type="text" id="company-phone" name="phone" value="{{ old('phone', $pageData->phone) }}" class="form-control" placeholder="" required>
                    </div>                    

                    <div class="mb-3 form-group">
                        <label for="company-email" class="form-label">Enquiry Email <span class="text-danger">*</span></label>
                        <input type="email" id="company-email" name="email" value="{{ old('email', $pageData->email) }}" class="form-control" placeholder="" required>
                    </div>

                    <div class="mb-3 form-group">
                        <label for="company-sales-partnership-email" class="form-label">Sales &amp; Partnership Email</label>
                        <input type="email"
                               id="company-sales-partnership-email"
                               name="meta[sales_partner_email]"
                               value="{{ old('meta.sales_partner_email', $pageData->meta->where('meta_key', 'sales_partner_email')->first()->meta_value ?? '') }}"
                               class="form-control"
                               placeholder="">
                    </div>

                    <div class="mb-3 form-group">
                        <label for="company-technical-support-email" class="form-label">Technical Support Email</label>
                        <input type="email"
                               id="company-technical-support-email"
                               name="meta[technical_support_email]"
                               value="{{ old('meta.technical_support_email', $pageData->meta->where('meta_key', 'technical_support_email')->first()->meta_value ?? '') }}"
                               class="form-control"
                               placeholder="">
                    </div>

                    <div class="mb-3 form-group">
                        <label for="company-careers-email" class="form-label">Careers Email</label>
                        <input type="email"
                               id="company-careers-email"
                               name="meta[careers_email]"
                               value="{{ old('meta.careers_email', $pageData->meta->where('meta_key', 'careers_email')->first()->meta_value ?? '') }}"
                               class="form-control"
                               placeholder="">
                    </div>

                </div>
            </div>

            <!-- Content Sections -->
            <div class="card">
                <div class="card-body">
                    {{-- <h5 class="text-uppercase bg-light p-2 mt-0 mb-3">Content Sections</h5> --}}

                    <div class="row">
                        <div class="col-md-12">
                            <h4 class="text-uppercase bg-light p-2 mt-0 mb-3">Questions Section</h4>
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="meta[questions_title]" value="{{ old('meta.questions_title', $pageData->meta->where('meta_key', 'questions_title')->first()->meta_value ?? '') }}">
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <label class="form-label">Sub Title</label>
                            <input type="text" class="form-control" name="meta[questions_subtitle]" value="{{ old('meta.questions_subtitle', $pageData->meta->where('meta_key', 'questions_subtitle')->first()->meta_value ?? '') }}">
                        </div>
                        <div class="col-md-12 form-group mb-2">
                            <label class="form-label">URL</label>
                            <input type="text" class="form-control" name="meta[questions_url]" value="{{ old('meta.questions_url', $pageData->meta->where('meta_key', 'questions_url')->first()->meta_value ?? '') }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">

                    <div class="row">
                        <div class="col-md-12">
                            <h4 class="text-uppercase bg-light p-2 mt-0 mb-3">Subscribe Section</h4>
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="meta[subscribe_title]" value="{{ old('meta.subscribe_title', $pageData->meta->where('meta_key', 'subscribe_title')->first()->meta_value ?? '') }}">
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <label class="form-label">Sub Title</label>
                            <input type="text" class="form-control" name="meta[subscribe_subtitle]" value="{{ old('meta.subscribe_subtitle', $pageData->meta->where('meta_key', 'subscribe_subtitle')->first()->meta_value ?? '') }}">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">

                    <div class="row">
                        <div class="col-md-12">
                            <h4 class="text-uppercase bg-light p-2 mt-0 mb-3">Guidance Section</h4>
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="meta[guidance_title]" value="{{ old('meta.guidance_title', $pageData->meta->where('meta_key', 'guidance_title')->first()->meta_value ?? '') }}">
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <label class="form-label">URL</label>
                            <input type="text" class="form-control" name="meta[guidance_url]" value="{{ old('meta.guidance_url', $pageData->meta->where('meta_key', 'guidance_url')->first()->meta_value ?? '') }}">
                        </div>
                    </div>

                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <h4 class="text-uppercase bg-light p-2 mt-0 mb-3">Industry Detail Guidance Section</h4>
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="meta[industry_detail_guidance_title]" value="{{ old('meta.industry_detail_guidance_title', $pageData->meta->where('meta_key', 'industry_detail_guidance_title')->first()->meta_value ?? '') }}">
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <label class="form-label">URL</label>
                            <input type="text" class="form-control" name="meta[industry_detail_guidance_url]" value="{{ old('meta.industry_detail_guidance_url', $pageData->meta->where('meta_key', 'industry_detail_guidance_url')->first()->meta_value ?? '') }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <h4 class="text-uppercase bg-light p-2 mt-0 mb-3">Contact Section</h4>
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="meta[contact_title]" value="{{ old('meta.contact_title', $pageData->meta->where('meta_key', 'contact_title')->first()->meta_value ?? '') }}">
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <label class="form-label">Sub Title</label>
                            <input type="text" class="form-control" name="meta[contact_subtitle]" value="{{ old('meta.contact_subtitle', $pageData->meta->where('meta_key', 'contact_subtitle')->first()->meta_value ?? '') }}">
                        </div>
                        <div class="col-md-12 form-group mb-2">
                            <label class="form-label">Card Title</label>
                            <input type="text" class="form-control" name="meta[contact_card_title]" value="{{ old('meta.contact_card_title', $pageData->meta->where('meta_key', 'contact_card_title')->first()->meta_value ?? '') }}">
                        </div>
                        <div class="col-md-12 form-group mb-2">
                            <label class="form-label">Card Description</label>
                            <textarea name="meta[contact_card_description]" class="form-control text-editor" rows="3">{{ old('meta.contact_card_description', $pageData->meta->where('meta_key', 'contact_card_description')->first()->meta_value ?? '') }}</textarea>
                        </div>
                        <div class="col-md-12 form-group mb-2">
                            <label class="form-label">Card URL</label>
                            <input type="text" class="form-control" name="meta[contact_card_url]" value="{{ old('meta.contact_card_url', $pageData->meta->where('meta_key', 'contact_card_url')->first()->meta_value ?? '') }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    
                    <div class="row">
                        <div class="col-md-12">
                            <h4 class="text-uppercase bg-light p-2 mt-0 mb-3">Technical Experts Section</h4>
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="meta[technical_experts_title]" value="{{ old('meta.technical_experts_title', $pageData->meta->where('meta_key', 'technical_experts_title')->first()->meta_value ?? '') }}">
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <label class="form-label">Form Title</label>
                            <input type="text" class="form-control" name="meta[technical_experts_form_title]" value="{{ old('meta.technical_experts_form_title', $pageData->meta->where('meta_key', 'technical_experts_form_title')->first()->meta_value ?? '') }}">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Image</label>
                            <div class="form-group mb-2">
                                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                    <input type="hidden" name="meta[technical_experts_image]" value="{{ old('meta.technical_experts_image', $pageData->meta->where('meta_key', 'technical_experts_image')->first()->meta_value ?? '') }}" class="selected-files">
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
                        <input type="url" class="form-control" id="meta-instagram" name="meta[instagram_url]" value="{{ old('meta.instagram_url', $pageData->meta->where('meta_key', 'instagram_url')->first()->meta_value ?? '') }}" placeholder="Enter Instagram URL">
                    </div>
                    <div class="mb-3 form-group">
                        <label for="meta-x" class="form-label">X URL</label>
                        <input type="url" class="form-control" id="meta-x" name="meta[x_url]" value="{{ old('meta.x_url', $pageData->meta->where('meta_key', 'x_url')->first()->meta_value ?? '') }}" placeholder="Enter X URL">
                    </div>
                    <div class="mb-3 form-group">
                        <label for="meta-linkedin" class="form-label">LinkedIn URL</label>
                        <input type="url" class="form-control" id="meta-linkedin" name="meta[linkedin_url]" value="{{ old('meta.linkedin_url', $pageData->meta->where('meta_key', 'linkedin_url')->first()->meta_value ?? '') }}" placeholder="Enter LinkedIn URL">
                    </div>
                    <div class="mb-3 form-group">
                        <label for="meta-facebook" class="form-label">Facebook URL</label>
                        <input type="url" class="form-control" id="meta-facebook" name="meta[facebook_url]" value="{{ old('meta.facebook_url', $pageData->meta->where('meta_key', 'facebook_url')->first()->meta_value ?? '') }}" placeholder="Enter Facebook URL">
                    </div>
                    <div class="mb-3 form-group">
                        <label for="meta-youtube" class="form-label">YouTube URL</label>
                        <input type="url" class="form-control" id="meta-youtube" name="meta[youtube_url]" value="{{ old('meta.youtube_url', $pageData->meta->where('meta_key', 'youtube_url')->first()->meta_value ?? '') }}" placeholder="Enter YouTube URL">
                    </div>
                    <div class="mb-3 form-group">
                        <label for="meta-tiktok" class="form-label">TikTok URL</label>
                        <input type="url" class="form-control" id="meta-tiktok" name="meta[tiktok_url]" value="{{ old('meta.tiktok_url', $pageData->meta->where('meta_key', 'tiktok_url')->first()->meta_value ?? '') }}" placeholder="Enter TikTok URL">
                    </div>
                    <div class="mb-3 form-group">
                        <label for="meta-vimeo" class="form-label">Vimeo URL</label>
                        <input type="url" class="form-control" id="meta-vimeo" name="meta[vimeo_url]" value="{{ old('meta.vimeo_url', $pageData->meta->where('meta_key', 'vimeo_url')->first()->meta_value ?? '') }}" placeholder="Enter Vimeo URL">
                    </div>
                </div>
            </div>

            <!-- SEO -->
            {{-- <div class="card">
                <div class="card-body">
                    <h5 class="text-uppercase mt-0 mb-3 bg-light p-2">DEFAULT SEO</h5>
                    <div class="mb-3 form-group">
                        <label for="meta-title" class="form-label">Meta Title</label>
                        <input type="text" id="meta-title" name="meta_title" value="{{ old('meta_title', $pageData->meta_title) }}" class="form-control" placeholder="Enter meta title">
                    </div>
                    <div class="mb-3 form-group">
                        <label for="meta-description" class="form-label">Meta Description</label>
                        <textarea class="form-control" id="meta-description" name="meta_description" rows="3" placeholder="Enter meta description">{{ old('meta_description', $pageData->meta_description) }}</textarea>
                    </div>

                    <hr class="my-3">

                    <div class="mb-3 form-group">
                        <label for="meta-favicon" class="form-label">Favicon</label>
                        <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                            <div class="input-group-prepend">
                                <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                            </div>
                            <div class="form-control file-amount">{{ __('Choose File') }}</div>
                            <input type="hidden" id="meta-favicon" name="meta[favicon]" value="{{ old('meta.favicon', $pageData->meta->where('meta_key', 'favicon')->first()->meta_value ?? '') }}" class="selected-files">
                        </div>
                        <div class="file-preview box sm"></div>
                    </div>

                    <div class="mb-3 form-group">
                        <label for="meta-og-title" class="form-label">OG Title</label>
                        <input type="text" id="meta-og-title" name="meta[og_title]" value="{{ old('meta.og_title', $pageData->meta->where('meta_key', 'og_title')->first()->meta_value ?? '') }}" class="form-control" placeholder="Enter OG title">
                    </div>

                    <div class="mb-3 form-group">
                        <label for="meta-og-description" class="form-label">OG Description</label>
                        <textarea class="form-control" id="meta-og-description" name="meta[og_description]" rows="3" placeholder="Enter OG description">{{ old('meta.og_description', $pageData->meta->where('meta_key', 'og_description')->first()->meta_value ?? '') }}</textarea>
                    </div>

                    <div class="mb-3 form-group">
                        <label for="meta-og-image" class="form-label">OG Image</label>
                        <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                            <div class="input-group-prepend">
                                <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                            </div>
                            <div class="form-control file-amount">{{ __('Choose File') }}</div>
                            <input type="hidden" id="meta-og-image" name="meta[og_image]" value="{{ old('meta.og_image', $pageData->meta->where('meta_key', 'og_image')->first()->meta_value ?? '') }}" class="selected-files">
                        </div>
                        <div class="file-preview box sm"></div>
                    </div>

                    <div class="mb-3 form-group">
                        <label for="meta-twitter-title" class="form-label">Twitter Title</label>
                        <input type="text" id="meta-twitter-title" name="meta[twitter_title]" value="{{ old('meta.twitter_title', $pageData->meta->where('meta_key', 'twitter_title')->first()->meta_value ?? '') }}" class="form-control" placeholder="Enter Twitter title">
                    </div>

                    <div class="mb-3 form-group">
                        <label for="meta-twitter-description" class="form-label">Twitter Description</label>
                        <textarea class="form-control" id="meta-twitter-description" name="meta[twitter_description]" rows="3" placeholder="Enter Twitter description">{{ old('meta.twitter_description', $pageData->meta->where('meta_key', 'twitter_description')->first()->meta_value ?? '') }}</textarea>
                    </div>

                    <div class="mb-3 form-group">
                        <label for="meta-twitter-image" class="form-label">Twitter Image</label>
                        <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                            <div class="input-group-prepend">
                                <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                            </div>
                            <div class="form-control file-amount">{{ __('Choose File') }}</div>
                            <input type="hidden" id="meta-twitter-image" name="meta[twitter_image]" value="{{ old('meta.twitter_image', $pageData->meta->where('meta_key', 'twitter_image')->first()->meta_value ?? '') }}" class="selected-files">
                        </div>
                        <div class="file-preview box sm"></div>
                    </div>

                    <div class="mb-3 form-group">
                        <label for="meta-schema-json" class="form-label">Schema JSON</label>
                        <textarea class="form-control" id="meta-schema-json" name="meta[schema_json]" rows="4" placeholder="Enter JSON-LD only (no &lt;script&gt; wrapper)">{{
                            old(
                                'meta.schema_json',
                                $pageData->meta->where('meta_key', 'schema_json')->first()->meta_value ?? ''
                            )
                        }}</textarea>
                    </div>
                </div>
            </div> --}}
            
            <!-- Submit Button -->
            <div class="text-end">
                <button type="submit" class="btn btn-primary w-100">Update</button>
            </div>
        </div>
    </div>
</form>

<script defer>
    initValidate('.form');
</script>
@endsection
