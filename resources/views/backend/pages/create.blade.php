@extends('backend.layouts.app')

@section('content')
<div class="page-title-head d-flex align-items-center gap-2">
    <div class="flex-grow-1">
        <h4 class="fs-16 text-uppercase fw-bold mb-0">{{$moduleName}} / Create</h4>
    </div>
	<div class="text-end">
		<ol class="breadcrumb m-0 py-0 fs-13">
			<li class="breadcrumb-item"><a href="{{ route($routeName . '.index') }}">Back to {{$moduleName}} list</a></li>
		</ol>
	</div>    
</div>

<form class="form" action="{{ route($routeName . '.store') }}" method="POST">
    @include('backend.includes.alert-message')
    @csrf
    <div class="row">
        <!-- Company Details -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-uppercase bg-light p-2 mt-0 mb-2">Primary section</h5>
                    <div class="mb-2 form-group">
                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" id="title" name="title" value="{{ old('title') }}" class="form-control" placeholder="Enter page title" required>
                    </div>

                    <div class="mb-2 form-group">
                        <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                        <textarea name="content" class="form-control text-editor" rows="4" required>{{ old('content') }}</textarea>
                    </div>                   
                </div>
            </div>            
        </div>
        
        <!-- Secondary Meta Data -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-uppercase mt-0 mb-2 bg-light p-2">Setting Section</h5>

                    <!-- Layout Dropdown -->
                    <div class="form-group mb-2">
                        <label for="layout" class="form-label">Layout <span class="text-danger">*</span></label>
                        @php
                            // For create page we currently allow only the default layout
                            $layouts = getPageLayouts(['default']);
                        @endphp

                        <select name="layout" class="form-select select2" required>
                            @foreach ($layouts as $layout => $layoutData)
                                <option
                                    value="{{ $layout }}"
                                    data-description="{{ $layoutData['description'] ?? '' }}"
                                >
                                    {{ $layoutData['label'] ?? ucfirst($layout) }}
                                </option>
                            @endforeach
                        </select>
                    </div> 

                    <div class="mb-2 form-group">
                        <label for="slug" class="form-label">Slug <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="slug" name="slug" value="{{ old('slug') }}" placeholder="Enter Slug" required>
                    </div>

                    <!-- Company (dropdown) -->
                    <div class="form-group mb-2 d-none">
                        <label for="company_id" class="form-label">School <span class="text-danger">*</span></label>
                        <select name="company_id" class="form-select select2" required>
                            @foreach (getCompanyList() as $index => $row)
                                <option value="{{ $row->id }}" 
                                    @if(auth()->user()->company_id == $row->id) selected @endif>
                                    {{ $row->name }}
                                </option>
                            @endforeach
                        </select>
                    </div> 
                    
                    <!-- Status -->
                    <div class="form-group mb-2">
                        <label for="is_active" class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="is_active" class="form-select select2" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>                   

                </div>
            </div>

            <!-- SEO -->
            <div class="card">
                <div class="card-body">
                    <h5 class="text-uppercase mt-0 mb-2 bg-light p-2">SEO Section</h5>
                    <div class="mb-2 form-group">
                        <label for="seo_title" class="form-label">Meta Title</label>
                        <input type="text" id="seo_title" name="seo_title" value="{{ old('seo_title') }}" class="form-control" placeholder="Enter meta title">
                    </div>
                    <div class="mb-2 form-group">
                        <label for="seo_description" class="form-label">Meta Description</label>
                        <textarea class="form-control" id="seo_description" name="seo_description" rows="3" placeholder="Enter meta description">{{ old('seo_description') }}</textarea>
                    </div>
                    {{--<div class="mb-2 form-group">
                        <label for="seo_schema" class="form-label">Schema Markup</label>
                        <textarea class="form-control" id="seo_schema" name="seo_schema" rows="3" placeholder="Enter schema markup">{{ old('seo_schema') }}</textarea>
                    </div>--}}                    
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
