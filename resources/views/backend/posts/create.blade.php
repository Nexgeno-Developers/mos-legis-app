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
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-uppercase bg-light p-2 mt-0 mb-2">Primary section</h5>
                    <div class="mb-2 form-group">
                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" id="title" name="title" value="{{ old('title') }}" class="form-control" placeholder="Enter post title" required>
                    </div>

                    <div id="layout-fields-container">
                        @include('backend.posts.edit-layouts.' . $postData->layout)
                    </div>

                    <div class="mb-2 form-group">
                        <label for="content" class="form-label">Content</label>
                        <textarea name="content" class="form-control text-editor" rows="4">{{ old('content') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-uppercase mt-0 mb-2 bg-light p-2">Setting Section</h5>

                    <div class="form-group mb-2">
                        <label for="layout" class="form-label">Layout <span class="text-danger">*</span></label>
                        @php
                            $layouts = getPostLayouts();
                        @endphp
                        <select name="layout" class="form-select select2" required>
                            @foreach ($layouts as $layout => $layoutData)
                                <option value="{{ $layout }}" @selected($postData->layout === $layout)>
                                    {{ $layoutData['label'] ?? ucfirst($layout) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-2 form-group">
                        <label for="slug" class="form-label">Slug <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="slug" name="slug" value="{{ old('slug') }}" placeholder="Enter Slug" required>
                        <div class="form-check mt-2 d-none">
                            <input class="form-check-input" type="checkbox" id="auto_slug_enabled" name="auto_slug_enabled" value="1" @checked(old('auto_slug_enabled'))>
                            <label class="form-check-label" for="auto_slug_enabled">Auto generate slug with selected category</label>
                        </div>
                        <div id="auto-slug-preview d-none" class="mt-2 p-2 bg-light border rounded small d-none"></div>
                    </div>

                    <div class="mb-2 form-group d-none">
                        <label for="language" class="form-label">Language</label>
                        <input type="text" class="form-control" id="language" name="language" value="{{ old('language', 'en') }}" placeholder="en">
                    </div>

                    <div class="mb-2 form-group">
                        <label for="author_id" class="form-label">Author <span class="text-danger">*</span></label>
                        <select name="author_id" class="form-select select2" required>
                            <option value="">-- Select Author --</option>
                            @foreach ($authors as $author)
                                <option value="{{ $author->id }}" @selected(old('author_id') == $author->id)>{{ $author->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    @php
                        $oldCategoryIds = old('category_ids', []);
                        $oldCategoryIds = is_array($oldCategoryIds) ? $oldCategoryIds : [];
                        $oldTagIds = old('tag_ids', []);
                        $oldTagIds = is_array($oldTagIds) ? $oldTagIds : [];
                    @endphp
                    @include('backend.posts.partials.category-single-select', ['categories' => $categories, 'oldCategoryIds' => $oldCategoryIds])

                    <div class="mb-2 form-group d-none">
                        <label for="tag_ids" class="form-label">Tags</label>
                        <select name="tag_ids[]" class="form-select select2" multiple>
                            @foreach ($tags as $tag)
                                <option value="{{ $tag->id }}" @if(in_array($tag->id, $oldTagIds, true)) selected @endif>{{ $tag->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-2 form-group clearfix">
                        <label for="featured_image" class="form-label">Featured Image</label>
                        <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                            <div class="input-group-prepend">
                                <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                            </div>
                            <div class="form-control file-amount">{{ __('Choose File') }}</div>
                            <input type="hidden" name="featured_image" class="selected-files" value="{{ old('featured_image') }}">
                        </div>
                        <div class="file-preview box sm"></div>
                    </div>

                    <div class="mb-2 form-group">
                        <label for="published_at" class="form-label">Published At</label>
                        <input type="datetime-local" class="form-control" name="published_at" value="{{ old('published_at') }}">
                    </div>

                    <div class="form-group mb-2 d-none">
                        <label for="company_id" class="form-label">Company <span class="text-danger">*</span></label>
                        <select name="company_id" class="form-select select2" required>
                            @foreach (getCompanyList() as $row)
                                <option value="{{ $row->id }}" @if(auth()->user()->company_id == $row->id) selected @endif>
                                    {{ $row->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-2">
                        <label for="is_active" class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="is_active" class="form-select select2" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>

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
                    {{-- <div class="mb-2 form-group">
                        <label for="seo_keywords" class="form-label">Meta Keywords</label>
                        <input type="text" id="seo_keywords" name="seo_keywords" value="{{ old('seo_keywords') }}" class="form-control" placeholder="Enter meta keywords">
                    </div>
                    <div class="mb-2 form-group">
                        <label for="seo_schema" class="form-label">Schema Markup</label>
                        <textarea class="form-control" id="seo_schema" name="seo_schema" rows="3" placeholder="Enter schema markup">{{ old('seo_schema') }}</textarea>
                    </div> --}}
                </div>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary w-100">Create</button>
            </div>
        </div>
    </div>
</form>

<script defer>
    initValidate('.form');

    (function() {
        const slugInput = document.getElementById('slug');
        const autoCheckbox = document.getElementById('auto_slug_enabled');
        const preview = document.getElementById('auto-slug-preview');
        if (!slugInput || !autoCheckbox || !preview) return;

        const normalizeSlug = (value) => {
            return (value || '')
                .toString()
                .trim()
                .replace(/^\/+|\/+$/g, '')
                .replace(/\s+/g, '-')
                .toLowerCase();
        };

        const getSelectedCategories = () => {
            return Array.from(document.querySelectorAll('.post-category-checkbox:checked')).map(cb => ({
                name: cb.dataset.name || '',
                slug: cb.dataset.slug || '',
            }));
        };

        const renderPreview = () => {
            if (!autoCheckbox.checked) {
                preview.classList.add('d-none');
                preview.innerHTML = '';
                return;
            }

            const categories = getSelectedCategories();
            if (categories.length === 0) {
                preview.classList.add('d-none');
                preview.innerHTML = '';
                return;
            }

            const baseSlug = normalizeSlug(slugInput.value);
            const items = categories.map((category) => {
                const catSlug = normalizeSlug(category.slug || category.name);
                const full = baseSlug ? `${catSlug}/${baseSlug}` : `${catSlug}/`;
                return `<div class="mb-1"><code>${full}</code></div>`;
            }).join('');

            preview.classList.remove('d-none');
            preview.innerHTML = `
                <div class="fw-semibold text-muted mb-1">Auto Generated Slug${categories.length > 1 ? 's' : ''}</div>
                ${items}
            `;
        };

        const handleAutoToggle = () => {
            if (autoCheckbox.checked && getSelectedCategories().length === 0) {
                toastr.error('Please select category first');
                autoCheckbox.checked = false;
                renderPreview();
                return;
            }
            renderPreview();
        };

        autoCheckbox.addEventListener('change', handleAutoToggle);
        slugInput.addEventListener('input', renderPreview);
        document.addEventListener('change', function(e) {
            if (!e.target.classList.contains('post-category-checkbox')) return;
            if (autoCheckbox.checked && getSelectedCategories().length === 0) {
                toastr.error('Please select category first');
                autoCheckbox.checked = false;
            }
            renderPreview();
        });

        renderPreview();
    })();
</script>
@endsection
