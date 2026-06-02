<div class="col-12 col-sm-3 col-md-3 col-lg-3">
    <div class="card overflow-hidden">
        <div class="card-body">
            <a href="{{ $url }}">
            <h5 class="text-muted fs-13 text-uppercase" title="{{ $name }}">{{ $name }}</h5>
            <div class="d-flex align-items-center gap-2 my-2 py-1">
                <div class="user-img fs-42 flex-shrink-0">
                    <span class="avatar-title text-bg-primary rounded-circle fs-22">
                        <i class="{{ $icon }}"></i>
                    </span>
                </div>
                <h3 class="mb-0 fw-bold text-dark">{{ $count }}</h3>
            </div>
            </a>
        </div>
    </div>
</div>
