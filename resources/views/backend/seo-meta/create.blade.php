<form class="form" id="create" action="{{ route($module . '.store') }}" method="POST">
    @csrf
    <div class="row">
        <div class="col-md-6">
            <div class="form-group mb-2">
                <label class="form-label">Slug <span class="text-danger">*</span></label>
                <input name="slug" type="text" class="form-control" maxlength="255" required>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group mb-2">
                <label class="form-label">Meta Title</label>
                <input name="meta_title" type="text" class="form-control" maxlength="255">
            </div>
        </div>

        <div class="col-md-12">
            <div class="form-group mb-2">
                <label class="form-label">Meta Description</label>
                <textarea name="meta_description" class="form-control" rows="3"></textarea>
            </div>
        </div>

        <div class="col-md-12">
            <div class="form-group mb-2">
                <label class="form-label">Meta Keywords</label>
                <textarea name="meta_keywords" class="form-control" rows="2"></textarea>
            </div>
        </div>

        <div class="col-md-12">
            <div class="form-group mb-2">
                <label class="form-label">Canonical URL</label>
                <input name="canonical_url" type="text" class="form-control" maxlength="255">
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group mb-2">
                <label class="form-label">Robots Index</label>
                <select name="robots_index" class="form-select select2">
                    <option value="index" selected>index</option>
                    <option value="noindex">noindex</option>
                </select>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group mb-2">
                <label class="form-label">Robots Follow</label>
                <select name="robots_follow" class="form-select select2">
                    <option value="follow" selected>follow</option>
                    <option value="nofollow">nofollow</option>
                </select>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group mb-2">
                <label class="form-label">OG Title</label>
                <input name="og_title" type="text" class="form-control" maxlength="255">
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group mb-2">
                <label class="form-label">Twitter Title</label>
                <input name="twitter_title" type="text" class="form-control" maxlength="255">
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group mb-2">
                <label class="form-label">OG Image</label>
                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                    <div class="input-group-prepend">
                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                    </div>
                    <div class="form-control file-amount">{{ __('Choose File') }}</div>
                    <input type="hidden" name="og_image" class="selected-files">
                </div>
                <div class="file-preview box sm"></div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group mb-2">
                <label class="form-label">Twitter Image</label>
                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                    <div class="input-group-prepend">
                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ __('Browse') }}</div>
                    </div>
                    <div class="form-control file-amount">{{ __('Choose File') }}</div>
                    <input type="hidden" name="twitter_image" class="selected-files">
                </div>
                <div class="file-preview box sm"></div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group mb-2">
                <label class="form-label">OG Description</label>
                <textarea name="og_description" class="form-control" rows="3"></textarea>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group mb-2">
                <label class="form-label">Twitter Description</label>
                <textarea name="twitter_description" class="form-control" rows="3"></textarea>
            </div>
        </div>

        <div class="col-md-12">
            <div class="form-group mb-2">
                <label class="form-label">Schema JSON</label>
                <textarea name="schema_json" class="form-control" rows="4" placeholder='{"@context":"https://schema.org"}'></textarea>
            </div>
        </div>

        <div class="col-md-12 d-none">
            <div class="form-group mb-2">
                <label class="form-label">Sitemap Priority</label>
                <input name="sitemap_priority" type="number" class="form-control" min="0" max="1" step="0.1">
            </div>
        </div>

        <div class="col-md-12">
            <div class="text-center mt-1">
                <button type="submit" class="btn btn-primary">{{ __('labels.create') }}</button>
            </div>
        </div>
    </div>
</form>

<script>
$(document).ready(function() {
    initValidate('#create');
    initSelect2('.select2');
    initAizPlugins();

    $("#create").submit(function(e) {
        var form = $(this);
        ajaxSubmit(e, form, callbackCreateForm);
    });

    const callbackCreateForm = function(response) {
        setTimeout(function() {
            location.reload();
        }, 1500);
    }
});
</script>
