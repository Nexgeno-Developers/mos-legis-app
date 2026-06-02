@extends('backend.layouts.app')

@section('content')
<div class="page-title-head d-flex align-items-center gap-2">
	<div class="flex-grow-1">
		<h4 class="fs-16 text-uppercase fw-bold mb-0">New {{$moduleName}}</h4>
	</div>

	<div class="text-end">
		<ol class="breadcrumb m-0 py-0 fs-13">
			<li class="breadcrumb-item"><a href="{{ route('uploaded-files.index') }}">Back to uploaded files</a></li>
		</ol>
	</div>
</div>

<div class="row">
	<div class="col-12">
		<div class="card">
			<div class="card-body">
				<div id="aiz-upload-files" class="h-420px" style="min-height: 65vh">
				
				</div>
            </div>
	    </div>
	</div>
</div>

<script type="text/javascript" defer>
	$(document).ready(function() {
		AIZ.plugins.aizUppy();
	});
</script>
@endsection