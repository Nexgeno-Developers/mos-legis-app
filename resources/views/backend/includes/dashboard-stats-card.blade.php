<div class="col-12 mb-3">
    <div class="overflow-hidden">
        <div class="">
            <div class="d-flex align-items-center gap-2 mb-2">
                <h5 class="text-uppercase fw-bold mb-0 fs-14">{{ $name }}</h5>
            </div>
            <div class="row g-3">
                @foreach ($stats as $stat)
                <div class="col-2">
                    <div class="card overflow-hidden mb-0 border h-100 w-100">
                        <div class="card-body py-3 d-flex flex-column h-100">
                            @if (!empty($stat['url']))
                            <a href="{{ $stat['url'] }}" class="text-reset text-decoration-none d-flex flex-column h-100">
                            @else
                            <div class="d-flex flex-column h-100">
                            @endif
                                <h6 class="text-muted fs-12 text-uppercase mb-2 flex-grow-1" title="{{ $stat['label'] }}">{{ $stat['label'] }}</h6>
                                <div class="d-flex align-items-center gap-2 mt-auto">
                                    <div class="user-img flex-shrink-0">
                                        <span class="avatar-title text-bg-light text-primary rounded-circle fs-16">
                                            <i class="{{ $stat['icon'] ?? $icon }}"></i>
                                        </span>
                                    </div>
                                    <h4 class="mb-0 fw-bold text-dark">{{ $stat['count'] }}</h4>
                                </div>
                            @if (!empty($stat['url']))
                            </a>
                            @else
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
