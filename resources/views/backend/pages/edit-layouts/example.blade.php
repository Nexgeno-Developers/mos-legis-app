@php 
    $input_text = $pageData->meta->where('meta_key', 'input_text')->first()->meta_value ?? '';
    $input_texteditor = $pageData->meta->where('meta_key', 'input_texteditor')->first()->meta_value ?? '';

    $single_image = $pageData->meta->where('meta_key', 'single_image')->first()->meta_value ?? '';
    $multiple_image = $pageData->meta->where('meta_key', 'multiple_image')->first()->meta_value ?? '';

    $single_document = $pageData->meta->where('meta_key', 'single_document')->first()->meta_value ?? '';
    $multiple_document = $pageData->meta->where('meta_key', 'multiple_document')->first()->meta_value ?? '';

    $single_video = $pageData->meta->where('meta_key', 'single_video')->first()->meta_value ?? '';
    $multiple_video = $pageData->meta->where('meta_key', 'multiple_video')->first()->meta_value ?? '';

    $select_single = $pageData->meta->where('meta_key', 'select_single')->first()->meta_value ?? '';
    $select_multiple = json_decode($pageData->meta->where('meta_key', 'select_multiple')->first()->meta_value ?? '[]', true);
    $select_multiple = is_array($select_multiple) ? $select_multiple : [];

    $radio_option = $pageData->meta->where('meta_key', 'radio_option')->first()->meta_value ?? '';
    $checkbox_options = json_decode($pageData->meta->where('meta_key', 'checkbox_options')->first()->meta_value ?? '[]', true);
    $checkbox_options = is_array($checkbox_options) ? $checkbox_options : [];
    
    $tags = $pageData->meta->where('meta_key', 'tags')->first()->meta_value ?? '';

    $dynamic_field = json_decode($pageData->meta->where('meta_key', 'dynamic_field')->first()->meta_value ?? '[]', true);
@endphp

<div class="row">

    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Input & textareas</h4>
    </div>  

    <div class="col-md-12 form-group mb-2">
        <label for="name" class="form-label">Input Text<span class="text-danger">*</span></label>
        <input class="form-control" value="{{$input_text}}" name="meta[input_text]" type="text" required>
    </div> 
    
    <div class="col-md-12 form-group mb-2">
        <label for="content" class="form-label">Input Texteditor <span class="text-danger">*</span></label>
        <textarea name="meta[input_texteditor]" class="form-control text-editor" rows="4" required>{{$input_texteditor}}</textarea>
    </div>

</div>

<div class="row">

    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Select, Radio, Checkboxes & Tags</h4>
    </div>

    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Select (Select2) <span class="text-danger">*</span></label>
        <select class="form-control select2" name="meta[select_single]" required>
            <option value="">Select an option</option>
            <option value="option_1" {{ $select_single == 'option_1' ? 'selected' : '' }}>Option 1</option>
            <option value="option_2" {{ $select_single == 'option_2' ? 'selected' : '' }}>Option 2</option>
            <option value="option_3" {{ $select_single == 'option_3' ? 'selected' : '' }}>Option 3</option>
        </select>
    </div>

    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Multiple Select (Select2) <span class="text-danger">*</span></label>
        <select class="form-control select2" name="meta[select_multiple][]" multiple required>
            <option value="option_1" {{ in_array('option_1', $select_multiple) ? 'selected' : '' }}>Option 1</option>
            <option value="option_2" {{ in_array('option_2', $select_multiple) ? 'selected' : '' }}>Option 2</option>
            <option value="option_3" {{ in_array('option_3', $select_multiple) ? 'selected' : '' }}>Option 3</option>
        </select>
    </div>

    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Radio Options <span class="text-danger">*</span></label>
        <div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="meta[radio_option]" id="radio_option_1" value="yes" {{ $radio_option == 'yes' ? 'checked' : '' }} required>
                <label class="form-check-label" for="radio_option_1">Yes</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="meta[radio_option]" id="radio_option_2" value="no" {{ $radio_option == 'no' ? 'checked' : '' }} required>
                <label class="form-check-label" for="radio_option_2">No</label>
            </div>
        </div>
    </div>

    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Checkboxes <span class="text-danger">*</span></label>
        <div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="meta[checkbox_options][]" id="checkbox_option_1" value="option_a" {{ in_array('option_a', $checkbox_options) ? 'checked' : '' }}>
                <label class="form-check-label" for="checkbox_option_1">Option A</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="meta[checkbox_options][]" id="checkbox_option_2" value="option_b" {{ in_array('option_b', $checkbox_options) ? 'checked' : '' }}>
                <label class="form-check-label" for="checkbox_option_2">Option B</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="meta[checkbox_options][]" id="checkbox_option_3" value="option_c" {{ in_array('option_c', $checkbox_options) ? 'checked' : '' }}>
                <label class="form-check-label" for="checkbox_option_3">Option C</label>
            </div>
        </div>
    </div>

    <div class="col-md-12 form-group mb-2">
        <label class="form-label">Tags</label>
        <input type="text" class="form-control aiz-tag-input" name="meta[tags]" value="{{ $tags }}" placeholder="Enter tags separated by commas">
    </div>

</div>

<div class="row">

    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Images, Documents, Videos</h4>
    </div>  

    <div class="col-md-12">
        <label for="name" class="form-label">Single Image<span class="text-danger">*</span></label>
        <div class="form-group mb-2">
            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                </div>
                <div class="form-control file-amount">{{ __('Choose File') }}</div>
                <input value="{{$single_image}}" type="hidden" name="meta[single_image]" class="selected-files" required>
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div>

    <div class="col-md-12">
        <label for="name" class="form-label">Multiple Images<span class="text-danger">*</span></label>
        <div class="form-group mb-2">
            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="true">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                </div>
                <div class="form-control file-amount">{{ __('Choose File') }}</div>
                <input value="{{$multiple_image}}" type="hidden" name="meta[multiple_image]" class="selected-files" required>
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div>    

    <div class="col-md-12">
        <label for="name" class="form-label">Single Document<span class="text-danger">*</span></label>
        <div class="form-group mb-2">
            <div class="input-group" data-toggle="aizuploader" data-type="document" data-multiple="false">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                </div>
                <div class="form-control file-amount">{{ __('Choose File') }}</div>
                <input value="{{$single_document}}" type="hidden" name="meta[single_document]" class="selected-files" required>
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div>

    <div class="col-md-12">
        <label for="name" class="form-label">Multiple Documents<span class="text-danger">*</span></label>
        <div class="form-group mb-2">
            <div class="input-group" data-toggle="aizuploader" data-type="document" data-multiple="true">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                </div>
                <div class="form-control file-amount">{{ __('Choose File') }}</div>
                <input value="{{$multiple_document}}" type="hidden" name="meta[multiple_document]" class="selected-files" required>
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div>

    <div class="col-md-12">
        <label for="name" class="form-label">Single Video<span class="text-danger">*</span></label>
        <div class="form-group mb-2">
            <div class="input-group" data-toggle="aizuploader" data-type="video" data-multiple="false">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                </div>
                <div class="form-control file-amount">{{ __('Choose File') }}</div>
                <input value="{{$single_video}}" type="hidden" name="meta[single_video]" class="selected-files" required>
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div>

    <div class="col-md-12">
        <label for="name" class="form-label">Multiple Videos<span class="text-danger">*</span></label>
        <div class="form-group mb-2">
            <div class="input-group" data-toggle="aizuploader" data-type="video" data-multiple="true">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                </div>
                <div class="form-control file-amount">{{ __('Choose File') }}</div>
                <input value="{{$multiple_video}}" type="hidden" name="meta[multiple_video]" class="selected-files" required>
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div>

</div>

<div class="row">
    <div class="col-md-12">
        <hr>
        <h4 class="text-primary">Dynamic Fields</h4>
    </div>    
    <div class="dynamic-fields-target">
        @if(isset($dynamic_field['itration']) && is_array($dynamic_field['itration']))
            @foreach($dynamic_field['itration'] as $index => $itration)
                <div class="row remove-parent">
                    <div class="col-md-11">
                        <div class="row">

                            <div class="col-md-12">
                                <!-- <label for="name" class="form-label">Dynamic Fields <span class="text-danger">*</span></label> -->
                                <input value="{{ $index }}" name="meta[dynamic_field][itration][]" type="hidden" required>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                        </div>
                                        <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                        <input type="hidden" 
                                            name="meta[dynamic_field][image][]" 
                                            class="selected-files" 
                                            value="{{ $dynamic_field['image'][$index] ?? '' }}" 
                                            required>
                                    </div>
                                    <div class="file-preview box sm"></div>
                                </div>
                            </div> 
                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <input value="{{ $dynamic_field['name'][$index] ?? '' }}" 
                                        name="meta[dynamic_field][name][]" 
                                        type="text" 
                                        class="form-control" 
                                        minlength="3" 
                                        maxlength="200" 
                                        placeholder="Enter name" 
                                        required>
                                </div>
                            </div>  
                            <div class="col-md-12">
                                <div class="form-group mb-2">
                                    <textarea  
                                        name="meta[dynamic_field][description][]" 
                                        class="form-control text-editor" 
                                        rows="1" 
                                        required>{{ $dynamic_field['description'][$index] ?? '' }}</textarea>
                                </div>
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
                        <!-- <label for="name" class="form-label">Dynamic Fields <span class="text-danger">*</span></label> -->
                        <input value="data" name="meta[dynamic_field][itration][]" type="hidden" required>
                    </div> 
                    <div class="col-md-6">
                        <div class="form-group mb-2">
                            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                                </div>
                                <div class="form-control file-amount">{{ __('Choose File') }}</div>
                                <input type="hidden" name="meta[dynamic_field][image][]" class="selected-files" required>
                            </div>
                            <div class="file-preview box sm"></div>
                        </div>
                    </div> 
                    <div class="col-md-6">
                        <div class="form-group mb-2">
                            <input value="" name="meta[dynamic_field][name][]" type="text" class="form-control" minlength="3" maxlength="200" placeholder="Enter name" required>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group mb-2">
                            <textarea value="" name="meta[dynamic_field][description][]" class="form-control text-editor" rows="1" placeholder="Enter description" required></textarea>
                        </div>
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
        data-target=".dynamic-fields-target">
        <i class="ti ti-plus"></i>
        <span class="ml-2">Add More</span>
    </button>     
</div>
