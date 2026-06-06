@extends('backend.layouts.app')

@section('content')
<div class="page-title-head d-flex align-items-center gap-2">
    <div class="flex-grow-1">
        <h4 class="fs-16 text-uppercase fw-bold mb-0">{{$moduleName}}</h4>
    </div>
	@can('companies view')
	<div class="text-end">
		<ol class="breadcrumb m-0 py-0 fs-13">
			<li class="breadcrumb-item"><a href="{{ route('companies.index') }}">Back to {{$moduleName}} list</a></li>
		</ol>
	</div>
	@endcan
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


                    <div class="mb-3 mt-1 form-group">
                        <label for="company-website" class="form-label">Website <span class="text-danger">*</span></label>
                        <input type="url" id="company-website" name="website" value="{{ $pageData->website }}" class="form-control" placeholder="" required>
                    </div>  

                    <div class="mb-3 form-group">
                        <label for="company-address" class="form-label">Address <span class="text-danger">*</span></label>
                        <textarea type="text" id="company-address" name="address" class="form-control" placeholder="e.g : 123 Main St, City, Country" required>{{ old('address', $pageData->address) }}</textarea>
                    </div>    
                    
                    <div class="mb-3 form-group">
                        <label for="company-phone" class="form-label">Phone <span class="text-danger">*</span></label>
                        <input type="text" id="company-phone" name="phone" value="{{ old('phone', $pageData->phone) }}" class="form-control" placeholder="" required>
                    </div>                    

                    <div class="mb-3 form-group">
                        <label for="company-email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" id="company-email" name="email" value="{{ old('email', $pageData->email) }}" class="form-control" placeholder="" required>
                    </div>

                    <div class="mb-3 form-group">
                        <label for="company-gstin" class="form-label">GSTIN</label>
                        <input type="text" id="company-gstin" name="meta[gstin]" value="{{ old('meta.gstin', $pageData->meta->where('meta_key', 'gstin')->first()->meta_value ?? '') }}" class="form-control" placeholder="e.g. 29AABCU9603R1ZX">
                    </div>

                </div>
            </div>
        </div>
        
        <!-- Secondary Meta Data -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-uppercase mt-0 mb-3 bg-light p-2">Bank Details</h5>
                    <div class="mb-3 form-group">
                        <label for="meta-bank-account-holder" class="form-label">Account Holder Name</label>
                        <input type="text" class="form-control" id="meta-bank-account-holder" name="meta[bank_account_holder_name]" value="{{ old('meta.bank_account_holder_name', $pageData->meta->where('meta_key', 'bank_account_holder_name')->first()->meta_value ?? '') }}" placeholder="e.g. WorkNest Spaces Pvt Ltd">
                    </div>
                    <div class="mb-3 form-group">
                        <label for="meta-bank-name" class="form-label">Bank Name</label>
                        <input type="text" class="form-control" id="meta-bank-name" name="meta[bank_name]" value="{{ old('meta.bank_name', $pageData->meta->where('meta_key', 'bank_name')->first()->meta_value ?? '') }}" placeholder="e.g. HDFC Bank">
                    </div>
                    <div class="mb-3 form-group">
                        <label for="meta-bank-account-number" class="form-label">Account Number</label>
                        <input type="text" class="form-control" id="meta-bank-account-number" name="meta[bank_account_number]" value="{{ old('meta.bank_account_number', $pageData->meta->where('meta_key', 'bank_account_number')->first()->meta_value ?? '') }}" placeholder="e.g. 50100123456789">
                    </div>
                    <div class="mb-3 form-group">
                        <label for="meta-bank-ifsc" class="form-label">IFSC</label>
                        <input type="text" class="form-control" id="meta-bank-ifsc" name="meta[bank_ifsc]" value="{{ old('meta.bank_ifsc', $pageData->meta->where('meta_key', 'bank_ifsc')->first()->meta_value ?? '') }}" placeholder="e.g. HDFC0001234">
                    </div>
                    <div class="mb-3 form-group">
                        <label for="meta-bank-branch" class="form-label">Branch</label>
                        <input type="text" class="form-control" id="meta-bank-branch" name="meta[bank_branch]" value="{{ old('meta.bank_branch', $pageData->meta->where('meta_key', 'bank_branch')->first()->meta_value ?? '') }}" placeholder="e.g. Indiranagar Branch">
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="text-uppercase mt-0 mb-3 bg-light p-2">Invoice Settings</h5>
                    <div class="mb-3 form-group">
                        <label for="meta-invoice-hsn-code" class="form-label">Invoice HSN Code</label>
                        <input type="text" class="form-control" id="meta-invoice-hsn-code" name="meta[invoice_hsn_code]" value="{{ old('meta.invoice_hsn_code', $pageData->meta->where('meta_key', 'invoice_hsn_code')->first()->meta_value ?? '') }}" placeholder="e.g. 997212">
                    </div>
                    <div class="mb-3 form-group">
                        <label for="meta-invoice-notes" class="form-label">Invoice Notes</label>
                        <textarea class="form-control" id="meta-invoice-notes" name="meta[invoice_notes]" rows="4" placeholder="Notes shown on invoice footer">{{ old('meta.invoice_notes', $pageData->meta->where('meta_key', 'invoice_notes')->first()->meta_value ?? '') }}</textarea>
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
            
            @can('companies edit')
            <!-- Submit Button -->
            <div class="text-end">
                <button type="submit" class="btn btn-primary w-100">Update</button>
            </div>
            @endcan
        </div>
    </div>
</form>

<script defer>
    initValidate('.form');
</script>
@endsection
