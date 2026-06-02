@extends('backend.layouts.app')

@section('content')
<div class="page-title-head d-flex align-items-center gap-2">
    <div class="flex-grow-1">
        <h4 class="fs-16 text-uppercase fw-bold mb-0">{{$moduleName}} / Edit</h4>
    </div>
    <div class="text-end">
        <ol class="breadcrumb m-0 py-0 fs-13">
            <li class="breadcrumb-item"><a href="{{ route($routeName . '.index') }}">Back to {{$moduleName}} list</a></li>
        </ol>
    </div>    
</div>

<form class="form" action="{{ route($routeName . '.update', $pageData->id) }}" method="POST">
    @include('backend.includes.alert-message')
    @csrf
    @method('PUT')
    <div class="row">
        <!-- Primary Section -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-uppercase bg-light p-2 mt-0 mb-2">Primary section</h5>
                    <div class="mb-2 form-group">
                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" id="title" name="title" value="{{ old('title', $pageData->title) }}" class="form-control" placeholder="Enter page title" required>
                    </div> 

                    <div id="layout-fields-container">
                        @include('backend.pages.edit-layouts.'.$pageData->layout)
                    </div>
                    
                    <div class="mb-2 form-group @if($pageData->layout != 'default') d-none @endif">
                        <label for="content" class="form-label">Content</label>
                        <textarea name="content" class="form-control text-editor" rows="4">{{ old('content', $pageData->content) }}</textarea>
                    </div>                    
                </div>
            </div>         
        </div>
        
        <!-- Setting Section -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-uppercase mt-0 mb-2 bg-light p-2">Setting Section</h5>

                    <!-- Layout Dropdown -->
                    <div class="form-group mb-2">
                        <label for="layout" class="form-label">Layout <span class="text-danger">*</span></label>
                        @php
                            $layouts = getPageLayouts();
                        @endphp

                        <select name="layout" class="form-select select2" required>
                            <option value="">-- Select Layout --</option>
                            @foreach ($layouts as $layout => $layoutData)
                                <option
                                    value="{{ $layout }}"
                                    data-description="{{ $layoutData['description'] ?? '' }}"
                                    {{ (isset($pageData) && $pageData->layout === $layout) ? 'selected' : '' }}
                                >
                                    {{ $layoutData['label'] ?? ucfirst($layout) }}
                                </option>
                            @endforeach
                        </select>
                    </div> 

                    <div class="mb-2 form-group">
                        <label for="slug" class="form-label">Slug <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="slug" name="slug" value="{{ old('slug', $pageData->slug) }}" placeholder="Enter Slug" @if( in_array($pageData->layout, ['home', 'thank_us'], true) ) readonly @endif required>
                    </div>

                    <!-- Company Dropdown -->
                    <div class="form-group mb-2 d-none">
                        <label for="company_id" class="form-label">School <span class="text-danger">*</span></label>
                        <select name="company_id" class="form-select select2" required>
                            @foreach (getCompanyList() as $index => $row)
                                <option value="{{ $row->id }}" 
                                    @if($pageData->company_id == $row->id) selected @endif>
                                    {{ $row->name }}
                                </option>
                            @endforeach
                        </select>
                    </div> 
                    
                    <!-- Status -->
                    <div class="form-group mb-2">
                        <label for="is_active" class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="is_active" class="form-select select2" required>
                            <option value="1" @if($pageData->is_active == 1) selected @endif>Active</option>
                            <option value="0" @if($pageData->is_active == 0) selected @endif>Inactive</option>
                        </select>
                    </div>                   

                </div>
            </div>

            <!-- SEO Section -->
            <div class="card">
                <div class="card-body">
                    <h5 class="text-uppercase mt-0 mb-2 bg-light p-2">SEO Section</h5>
                    <div class="mb-2 form-group">
                        <label for="seo_title" class="form-label">Meta Title</label>
                        <input type="text" id="seo_title" name="seo_title" value="{{ old('seo_title', $pageData->seo_title) }}" class="form-control" placeholder="Enter meta title">
                    </div>
                    <div class="mb-2 form-group">
                        <label for="seo_description" class="form-label">Meta Description</label>
                        <textarea class="form-control" id="seo_description" name="seo_description" rows="3" placeholder="Enter meta description">{{ old('seo_description', $pageData->seo_description) }}</textarea>
                    </div>
                    {{--<div class="mb-2 form-group">
                        <label for="seo_schema" class="form-label">Schema Markup</label>
                        <textarea class="form-control" id="seo_schema" name="seo_schema" rows="3" placeholder="Enter schema markup">{{ old('seo_schema', $pageData->seo_schema) }}</textarea>
                    </div>--}}
                </div>
            </div>
            
            <!-- Submit Button -->
            <div class="text-end">
                <button type="submit" class="btn btn-primary w-100">Update</button>
            </div>
        </div>
    </div>
</form>

<script defer>
    initValidate('.form');

    $(document).ready(function () {
        const $layoutSelect = $('select[name="layout"]');
        const $contentGroup = $('textarea[name="content"]').closest('.form-group');
        const $slugInput = $('#slug');
        const lockedSlugLayouts = ['home', 'thank_us'];
        let currentLayout = $layoutSelect.val();
        const pageId = {{ $pageData->id }};

        const toggleSlugReadonly = function (layout) {
            const shouldLock = lockedSlugLayouts.indexOf(layout) !== -1;
            $slugInput.prop('readonly', shouldLock);
        };

        // Toggle content visibility on initial load
        if ($layoutSelect.val() !== 'default') {
            $contentGroup.addClass('d-none');
        } else {
            $contentGroup.removeClass('d-none');
        }

        // Toggle slug readonly on initial load
        toggleSlugReadonly($layoutSelect.val());

        $layoutSelect.on('change', function () {
            const newLayout = $(this).val();

            if (!newLayout) {
                return;
            }

            if (!confirm('Are you sure you want to change the layout?\nSome layout-specific fields may change & lost their values on update.')) {
                // Revert to previous value if user cancels
                $(this).val(currentLayout).trigger('change.select2');
                toggleSlugReadonly(currentLayout);
                return;
            }

            $.ajax({
                url: "{{ route('pages.layout-fields', ['page' => $pageData->id]) }}",
                type: 'GET',
                data: { layout: newLayout },
                success: function (html) {
                    $('#layout-fields-container').html(html);

                    // Re-init plugins and events for newly injected fields
                    initAizPlugins();
                    initOtherPlugins();

                    // Toggle content visibility based on selected layout
                    if (newLayout !== 'default') {
                        $contentGroup.addClass('d-none');
                    } else {
                        $contentGroup.removeClass('d-none');
                    }

                    // Toggle slug readonly based on selected layout
                    toggleSlugReadonly(newLayout);
                    currentLayout = newLayout;
                },
                error: function () {
                    alert('Unable to load layout fields. Please try again.');
                    // Optionally revert select on error
                    $layoutSelect.val(currentLayout).trigger('change.select2');
                    toggleSlugReadonly(currentLayout);
                }
            });
        });
    });
</script>
@endsection
