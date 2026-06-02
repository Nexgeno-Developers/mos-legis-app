@php
    $sections = getPostLayoutSections($layoutKey ?? '');
@endphp

@if (empty($sections))
    <div class="row">
        <div class="col-md-12">
            <p class="text-muted mb-0">No layout fields defined for this layout.</p>
        </div>
    </div>
@endif

@foreach ($sections as $section)
    <div class="row">
        <div class="col-md-12">
            <hr>
            <h4 class="text-primary">{{ $section['title'] ?? 'Section' }}</h4>
        </div>

        @foreach (($section['fields'] ?? []) as $field)
            @php
                $key = $field['key'] ?? '';
                $type = $field['type'] ?? 'text';
                $label = $field['label'] ?? ucfirst($key);
                $placeholder = $field['placeholder'] ?? '';
                $required = isset($field['rules']) && str_contains($field['rules'], 'required');
                $value = post_meta_form_value($postData, $field);
                $inputId = 'meta_' . $key;
            @endphp

            @switch($type)
                @case('textarea')
                    <div class="col-md-12 form-group mb-2">
                        <label for="{{ $inputId }}" class="form-label">
                            {{ $label }} @if($required) <span class="text-danger">*</span> @endif
                        </label>
                        <textarea
                            id="{{ $inputId }}"
                            name="meta[{{ $key }}]"
                            class="form-control"
                            rows="3"
                            placeholder="{{ $placeholder }}"
                            @if($required) required @endif
                        >{{ old('meta.' . $key, $value) }}</textarea>
                    </div>
                    @break

                @case('editor')
                    <div class="col-md-12 form-group mb-2">
                        <label for="{{ $inputId }}" class="form-label">
                            {{ $label }} @if($required) <span class="text-danger">*</span> @endif
                        </label>
                        <textarea
                            id="{{ $inputId }}"
                            name="meta[{{ $key }}]"
                            class="form-control text-editor"
                            rows="4"
                            placeholder="{{ $placeholder }}"
                            @if($required) required @endif
                        >{{ old('meta.' . $key, $value) }}</textarea>
                    </div>
                    @break

                @case('select')
                    @php
                        $selectedValue = old('meta.' . $key, $value);
                    @endphp
                    <div class="col-md-12 form-group mb-2">
                        <label for="{{ $inputId }}" class="form-label">
                            {{ $label }} @if($required) <span class="text-danger">*</span> @endif
                        </label>
                        <select
                            id="{{ $inputId }}"
                            name="meta[{{ $key }}]"
                            class="form-select select2"
                            @if($required) required @endif
                        >
                            <option value="">-- Select --</option>
                            @foreach (($field['options'] ?? []) as $optionValue => $optionLabel)
                                <option value="{{ $optionValue }}" @selected($selectedValue == $optionValue)>
                                    {{ $optionLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @break

                @case('multiselect')
                    @php
                        $selectedValues = old('meta.' . $key, $value);
                        $selectedValues = is_array($selectedValues) ? $selectedValues : [];
                    @endphp
                    <div class="col-md-12 form-group mb-2">
                        <label for="{{ $inputId }}" class="form-label">
                            {{ $label }} @if($required) <span class="text-danger">*</span> @endif
                        </label>
                        <select
                            id="{{ $inputId }}"
                            name="meta[{{ $key }}][]"
                            class="form-select select2"
                            multiple
                            @if($required) required @endif
                        >
                            @foreach (($field['options'] ?? []) as $optionValue => $optionLabel)
                                <option value="{{ $optionValue }}" @if(in_array($optionValue, $selectedValues, true)) selected @endif>
                                    {{ $optionLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @break

                @case('date')
                    <div class="col-md-12 form-group mb-2">
                        <label for="{{ $inputId }}" class="form-label">
                            {{ $label }} @if($required) <span class="text-danger">*</span> @endif
                        </label>
                        <input
                            id="{{ $inputId }}"
                            type="date"
                            name="meta[{{ $key }}]"
                            class="form-control"
                            value="{{ old('meta.' . $key, $value) }}"
                            @if($required) required @endif
                        >
                    </div>
                    @break

                @case('time')
                    <div class="col-md-12 form-group mb-2">
                        <label for="{{ $inputId }}" class="form-label">
                            {{ $label }} @if($required) <span class="text-danger">*</span> @endif
                        </label>
                        <input
                            id="{{ $inputId }}"
                            type="time"
                            name="meta[{{ $key }}]"
                            class="form-control"
                            value="{{ old('meta.' . $key, $value) }}"
                            @if($required) required @endif
                        >
                    </div>
                    @break

                @case('image')
                    @php
                        $selectedValue = old('meta.' . $key, $value);
                    @endphp
                    <div class="col-md-12">
                        <label class="form-label">
                            {{ $label }} @if($required) <span class="text-danger">*</span> @endif
                        </label>
                        <div class="form-group mb-2">
                            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                </div>
                                <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                <input type="hidden" name="meta[{{ $key }}]" class="selected-files" value="{{ $selectedValue }}" @if($required) required @endif>
                            </div>
                            <div class="file-preview box sm"></div>
                        </div>
                    </div>
                    @break

                @case('images')
                    @php
                        $selectedValue = old('meta.' . $key, $value);
                    @endphp
                    <div class="col-md-12">
                        <label class="form-label">
                            {{ $label }} @if($required) <span class="text-danger">*</span> @endif
                        </label>
                        <div class="form-group mb-2">
                            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="true">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                </div>
                                <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                <input type="hidden" name="meta[{{ $key }}]" class="selected-files" value="{{ $selectedValue }}" @if($required) required @endif>
                            </div>
                            <div class="file-preview box sm"></div>
                        </div>
                    </div>
                    @break

                @case('repeater')
                    @php
                        $repeater = post_meta_form_value($postData, $field);
                        $repeater = is_array($repeater) ? $repeater : [];
                        $itrations = $repeater['itration'] ?? [];
                        $subFields = $field['fields'] ?? [];
                        $targetClass = 'repeater-target-' . $key;

                        $addMoreContent = '<div class="row remove-parent">';
                        $addMoreContent .= '<div class="col-md-11"><div class="row">';
                        $addMoreContent .= '<input value="data" name="meta[' . $key . '][itration][]" type="hidden">';

                        foreach ($subFields as $subField) {
                            $subKey = $subField['key'] ?? '';
                            if ($subKey === '') continue;
                            $subLabel = $subField['label'] ?? ucfirst($subKey);
                            $subType = $subField['type'] ?? 'text';
                            $subRequired = isset($subField['rules']) && str_contains($subField['rules'], 'required');
                            $requiredAttr = $subRequired ? 'required' : '';
                            $subPlaceholder = $subField['placeholder'] ?? '';

                            if ($subType === 'image') {
                                $addMoreContent .= '<div class="col-md-6">';
                                $addMoreContent .= '<label class="form-label">' . $subLabel . ($subRequired ? ' <span class="text-danger">*</span>' : '') . '</label>';
                                $addMoreContent .= '<div class="form-group mb-2">';
                                $addMoreContent .= '<div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">';
                                $addMoreContent .= '<div class="input-group-prepend"><div class="input-group-text bg-soft-secondary font-weight-medium">' . __('Browse') . '</div></div>';
                                $addMoreContent .= '<div class="form-control file-amount">' . __('Choose File') . '</div>';
                                $addMoreContent .= '<input type="hidden" name="meta[' . $key . '][' . $subKey . '][]" class="selected-files" ' . $requiredAttr . '>';
                                $addMoreContent .= '</div><div class="file-preview box sm"></div></div></div>';
                            } elseif ($subType === 'textarea') {
                                $addMoreContent .= '<div class="col-md-6">';
                                $addMoreContent .= '<label class="form-label">' . $subLabel . ($subRequired ? ' <span class="text-danger">*</span>' : '') . '</label>';
                                $addMoreContent .= '<textarea name="meta[' . $key . '][' . $subKey . '][]" class="form-control" rows="2" placeholder="' . $subPlaceholder . '" ' . $requiredAttr . '></textarea>';
                                $addMoreContent .= '</div>';
                            } else {
                                $addMoreContent .= '<div class="col-md-6">';
                                $addMoreContent .= '<label class="form-label">' . $subLabel . ($subRequired ? ' <span class="text-danger">*</span>' : '') . '</label>';
                                $addMoreContent .= '<input type="text" name="meta[' . $key . '][' . $subKey . '][]" class="form-control" placeholder="' . $subPlaceholder . '" ' . $requiredAttr . '>';
                                $addMoreContent .= '</div>';
                            }
                        }

                        $addMoreContent .= '</div></div>';
                        $addMoreContent .= '<div class="col-md-1 btn-dynamic-fields">';
                        $addMoreContent .= '<button type="button" class="btn btn-icon btn-circle btn-soft-danger" data-toggle="remove-parent" data-parent=".remove-parent">';
                        $addMoreContent .= '<i class="ti ti-x"></i></button></div></div>';
                    @endphp

                    <div class="col-md-12">
                        <label class="form-label">
                            {{ $label }} @if($required) <span class="text-danger">*</span> @endif
                        </label>
                    </div>
                    <div class="col-md-12 {{ $targetClass }}">
                        @if(is_array($itrations))
                            @foreach($itrations as $index => $itration)
                                <div class="row remove-parent">
                                    <div class="col-md-11">
                                        <div class="row">
                                            <input value="{{ $index }}" name="meta[{{ $key }}][itration][]" type="hidden">
                                            @foreach ($subFields as $subField)
                                                @php
                                                    $subKey = $subField['key'] ?? '';
                                                    $subLabel = $subField['label'] ?? ucfirst($subKey);
                                                    $subType = $subField['type'] ?? 'text';
                                                    $subPlaceholder = $subField['placeholder'] ?? '';
                                                    $subRequired = isset($subField['rules']) && str_contains($subField['rules'], 'required');
                                                    $subValues = $repeater[$subKey] ?? [];
                                                    $subValue = is_array($subValues) ? ($subValues[$index] ?? '') : '';
                                                @endphp
                                                @if($subType === 'image')
                                                    <div class="col-md-6">
                                                        <label class="form-label">
                                                            {{ $subLabel }} @if($subRequired) <span class="text-danger">*</span> @endif
                                                        </label>
                                                        <div class="form-group mb-2">
                                                            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                                                <div class="input-group-prepend">
                                                                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                                                </div>
                                                                <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                                                <input type="hidden" name="meta[{{ $key }}][{{ $subKey }}][]" class="selected-files" value="{{ $subValue }}" @if($subRequired) required @endif>
                                                            </div>
                                                            <div class="file-preview box sm"></div>
                                                        </div>
                                                    </div>
                                                @elseif($subType === 'textarea')
                                                    <div class="col-md-6">
                                                        <label class="form-label">
                                                            {{ $subLabel }} @if($subRequired) <span class="text-danger">*</span> @endif
                                                        </label>
                                                        <textarea name="meta[{{ $key }}][{{ $subKey }}][]" class="form-control" rows="2" placeholder="{{ $subPlaceholder }}" @if($subRequired) required @endif>{{ $subValue }}</textarea>
                                                    </div>
                                                @else
                                                    <div class="col-md-6">
                                                        <label class="form-label">
                                                            {{ $subLabel }} @if($subRequired) <span class="text-danger">*</span> @endif
                                                        </label>
                                                        <input type="text" name="meta[{{ $key }}][{{ $subKey }}][]" class="form-control" value="{{ $subValue }}" placeholder="{{ $subPlaceholder }}" @if($subRequired) required @endif>
                                                    </div>
                                                @endif
                                            @endforeach
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
                    <div class="col-md-12">
                        <button
                            type="button"
                            class="mt-1 btn btn-soft-success btn-icon w-100"
                            data-toggle="add-more"
                            data-limit="20"
                            data-content='{!! $addMoreContent !!}'
                            data-target=".{{ $targetClass }}">
                            <i class="ti ti-plus"></i>
                            <span class="ml-2">Add More</span>
                        </button>
                    </div>
                    @break

                @default
                    <div class="col-md-12 form-group mb-2">
                        <label for="{{ $inputId }}" class="form-label">
                            {{ $label }} @if($required) <span class="text-danger">*</span> @endif
                        </label>
                        <input
                            id="{{ $inputId }}"
                            type="text"
                            name="meta[{{ $key }}]"
                            class="form-control"
                            placeholder="{{ $placeholder }}"
                            value="{{ old('meta.' . $key, $value) }}"
                            @if($required) required @endif
                        >
                    </div>
            @endswitch
        @endforeach
    </div>
@endforeach
