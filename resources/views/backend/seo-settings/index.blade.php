@extends('backend.layouts.app')

@section('content')
<div class="page-title-head d-flex align-items-center gap-2">
    <div class="flex-grow-1">
        <h4 class="fs-16 text-uppercase fw-bold mb-0">{{ $module }}</h4>
        <small class="text-muted">Company ID: {{ $companyId }}</small>
    </div>
</div>

@include('backend.includes.alert-message')

<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header border-bottom border-dashed">
                <h5 class="mb-0">robots.txt</h5>
            </div>
            <div class="card-body">
                <form class="form" id="seo-settings-form" action="{{ route('seo-settings.update') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group mb-2">
                                <label class="form-label">robots.txt content</label>
                                <textarea name="content" class="form-control" rows="12" placeholder="User-agent: *&#10;Disallow:">{{ old('content', $setting->content) }}</textarea>
                                <small class="text-muted d-block mt-1">
                                    This content is served via <code>/api/v1/robots-txt</code> (use <code>?format=txt</code> for plain text).
                                </small>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="text-center mt-1">
                                <button type="submit" class="btn btn-primary">{{ __('labels.update') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    initValidate('#seo-settings-form');

    $("#seo-settings-form").submit(function(e) {
        var form = $(this);
        ajaxSubmit(e, form, callbackUpdateForm);
    });

    const callbackUpdateForm = function(response) {
        setTimeout(function() {
            location.reload();
        }, 1000);
    }
});
</script>
@endsection
