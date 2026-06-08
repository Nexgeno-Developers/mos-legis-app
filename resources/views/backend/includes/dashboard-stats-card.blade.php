<div class="col-12 mb-3">
    <div class="card overflow-hidden">
        <div class="card-body">
            <div class="d-flex align-items-center gap-2 mb-3">
                <div class="user-img fs-42 flex-shrink-0">
                    <span class="avatar-title text-bg-primary rounded-circle fs-18">
                        <i class="{{ $icon }}"></i>
                    </span>
                </div>
                <h5 class="text-uppercase fw-bold mb-0 fs-14">{{ $name }}</h5>
            </div>
            <div class="row g-3">
                @foreach ($stats as $stat)
                <div class="col-6 col-sm-4 col-lg">
                    <div class="card overflow-hidden mb-0 border">
                        <div class="card-body py-3">
                            @if (!empty($stat['url']))
                            <a href="{{ $stat['url'] }}" class="text-reset">
                            @endif
                                <h6 class="text-muted fs-12 text-uppercase mb-2" title="{{ $stat['label'] }}">{{ $stat['label'] }}</h6>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="user-img flex-shrink-0">
                                        <span class="avatar-title text-bg-light text-primary rounded-circle fs-16">
                                            <i class="{{ $stat['icon'] ?? $icon }}"></i>
                                        </span>
                                    </div>
                                    <h4 class="mb-0 fw-bold text-dark">{{ $stat['count'] }}</h4>
                                </div>
                            @if (!empty($stat['url']))
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
