<div class="modal fade" id="aizUploaderModal" data-bs-backdrop="static" role="dialog">
  <div class="modal-dialog modal-fullscreen" role="document">
    <div class="modal-content h-100">
      <div class="modal-header  pb-1 pt-1 bg-light">
        <div class="uppy-modal-nav">
          <ul class="nav nav-tabs border-0" role="tablist">
            <li class="nav-item">
              <a class="nav-link active font-weight-medium text-dark" data-bs-toggle="tab" href="#aiz-select-file">{{ __('Select File') }}</a>
            </li>
            <li class="nav-item">
              <a class="nav-link font-weight-medium text-dark" data-bs-toggle="tab" href="#aiz-upload-new">{{ __('Upload New') }}</a>
            </li>
            <li class="nav-item">
              <div class="text-muted font-weight-medium mt-1">
                Upload instruction: Images under 90 KB for best performance. PDFs 1.5 MB or less for best performance.
              </div>
            </li>
          </ul>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="tab-content h-100">
          <div class="tab-pane fade show active h-100" id="aiz-select-file">
            <div class="aiz-uploader-filter pt-1 pb-3 border-bottom mb-4">
              <div class="row align-items-center gutters-5 gutters-md-10 position-relative">
                <div class="col-xl-2 col-md-3 col-5">
                  <div>
                    <!-- Input -->
                    <select class="form-select form-select-sm aiz-selectpicker" name="aiz-uploader-sort">
                      <option value="newest" selected>{{ __('Sort by newest') }}</option>
                      <option value="oldest">{{ __('Sort by oldest') }}</option>
                      <option value="smallest">{{ __('Sort by smallest') }}</option>
                      <option value="largest">{{ __('Sort by largest') }}</option>
                    </select>
                    <!-- End Input -->
                  </div>
                </div>
                <div class="col-md-3 col-5">
                  <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="aiz-show-selected" name="aiz-show-selected">
                    <label class="form-check-label" for="aiz-show-selected">
                      {{ __('Selected Only') }}
                    </label>
                  </div>
                </div>
                <div class="col-md-4 col-xl-3 ms-auto me-0 col-2 position-static">
                  <div class="aiz-uploader-search text-right">
                    <input type="text" class="form-control form-control-sm" name="aiz-uploader-search" placeholder="{{ __('Search your files') }}">
                    <i class="search-icon d-md-none"><span></span></i>
                  </div>
                </div>
              </div>
            </div>
            <div class="aiz-uploader-all clearfix c-scrollbar-light">
              <div class="align-items-center d-flex h-100 justify-content-center w-100">
                <div class="text-center">
                  <h3>{{ __('No files found') }}</h3>
                </div>
              </div>
            </div>
          </div>

          <div class="tab-pane fade h-100" id="aiz-upload-new">
            <div id="aiz-upload-files" class="h-100"></div>
          </div>
        </div>
      </div>
      <div class="modal-footer justify-content-between bg-light">
        <div class="flex-grow-1 overflow-hidden d-flex">
          <div>
            <div class="aiz-uploader-selected">{{ __('0 File selected') }}</div>
            <button type="button" class="btn btn-link btn-sm p-0 aiz-uploader-selected-clear">{{ __('Clear') }}</button>
          </div>
          <div class="mb-0 ms-3">
            <button type="button" class="btn btn-sm btn-primary" id="uploader_prev_btn">{{ __('Prev') }}</button>
            <button type="button" class="btn btn-sm btn-primary" id="uploader_next_btn">{{ __('Next') }}</button>
          </div>
        </div>
        <button type="button" class="btn btn-sm btn-primary" data-toggle="aizUploaderAddSelected">{{ __('Add Files') }}</button>
      </div>
    </div>
  </div>
</div>
