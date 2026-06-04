@extends('backend.layouts.app')

@section('title', 'Dashboard')

@section('content')

@php
    use Illuminate\Support\Facades\Cache;
    use App\Models\Post;
    use App\Models\MenuItem;
    
    $mediaCount = Cache::remember('media_count_' . (auth()->id() ?? 'guest'), 86400, function () {
        return \App\Models\Upload::when(auth()->user()?->company_id, function ($query, $companyId) {
            return $query->where('user_id', auth()->id());
        })->count();
    });     
    
    $visitors = Cache::remember('visitors_count_' . (auth()->user()?->company_id ?? 'all'), 86400, function () {
        return \App\Models\Visitor::when(auth()->user()?->company_id, function ($query, $companyId) {
            return $query->where('company_id', auth()->user()->company_id);
        })->count();
    });
@endphp

<div class="page-title-head d-flex align-items-center gap-2">
    <div class="flex-grow-1">
        <h4 class="fs-16 text-uppercase fw-bold mb-0">Dashboard</h4>
    </div>
</div>

<div class="row justify-content-center">
    @include('backend.includes.dashboard-card', [
        'name' => 'Media Uploads',
        'icon' => 'ti ti-file-upload',
        'count' => $mediaCount,
        'url' => route('uploaded-files.index'),
    ])

    @can('visitors view')
    @include('backend.includes.dashboard-card', [
        'name' => 'Visitors',
        'icon' => 'ti ti-world',
        'count' => $visitors,
        'url' => route('visitors.index'),
    ])
    @endcan
</div>
@endsection
