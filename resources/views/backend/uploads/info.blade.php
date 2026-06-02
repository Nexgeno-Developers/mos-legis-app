<div>
	<div class="form-group mb-2">
		<label>{{ __('File Name') }}</label>
		<input type="text" class="form-control" value="{{ $file->file_name }}" disabled>
	</div>
	<div class="form-group mb-2">
		<label>{{ __('File Type') }}</label>
		<input type="text" class="form-control" value="{{ $file->type }}" disabled>
	</div>
	<div class="form-group mb-2">
		<label>{{ __('File Size') }}</label>
		<input type="text" class="form-control" value="{{ formatBytes($file->file_size) }}" disabled>
	</div>
	<div class="form-group mb-2">
		<label>{{ __('Uploaded By') }}</label>
		<input type="text" class="form-control" value="{{ $file->user->name }}" disabled>
	</div>
	<div class="form-group mb-2">
		<label>{{ __('Uploaded At') }}</label>
		<input type="text" class="form-control" value="{{ $file->created_at }}" disabled>
	</div>
	<div class="form-group text-center mb-2">
		@php
			if($file->file_original_name == null){
			    $file_name = __('Unknown');
			}else{
				$file_name = $file->file_original_name;
			}
		@endphp
		<a class="btn btn-secondary" href="{{ my_asset($file->file_name) }}" target="_blank" download="{{ $file_name }}.{{ $file->extension }}">{{ __('Download') }}</a>
	</div>
</div>