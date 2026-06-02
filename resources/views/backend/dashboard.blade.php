<!-- resources/views/backend/dashboard.blade.php -->
@extends('backend.layouts.app')

@section('title', 'Dashboard')

@section('content')

@php

    use Illuminate\Support\Facades\Cache;
    use App\Models\Post;
    use App\Models\MenuItem;


    // page count (24 hours cache)
    $pageCount = \App\Models\Page::when(auth()->user()?->company_id, function ($query, $companyId) {
        return $query->where('company_id', $companyId);
    }, function ($query) {
        //return $query->where('company_id', config('custom.company_id'));
    })->count();

    // SeoMeta count (24 hours cache)
    $seometaCount = Cache::remember('seometa_count', 86400, function () {
        return DB::table('seo_meta')->count();
    });    
    
    // Media count (24 hours cache, unique per user)
    $mediaCount = Cache::remember('media_count_' . (auth()->id() ?? 'guest'), 86400, function () {
        return \App\Models\Upload::when(auth()->user()?->company_id, function ($query, $companyId) {
            return $query->where('user_id', auth()->id());
        })->count();
    });     
    
    // Forms count (24 hours cache)
    $formCount = Cache::remember('forms_count_' . (auth()->user()?->company_id ?? 'all'), 86400, function () {
        return \App\Models\Form::when(auth()->user()?->company_id, function ($query, $companyId) {
            return $query->where('company_id', $companyId);
        })->count();
    });     

    // Post count (24 hours cache)
    $postCount = Cache::remember('posts_count_' . (auth()->user()?->company_id ?? 'all'), 86400, function () {
        return Post::when(auth()->user()?->company_id, function ($query, $companyId) {
            return $query->where('company_id', $companyId);
        })->count();
    });

    // Menu items count (24 hours cache)
    $menuCount = Cache::remember('menu_items_count', 86400, function () {
        return MenuItem::count();
    });
    
/*$visitors = Cache::remember('visitors_count_' . (auth()->user()?->company_id ?? 'all'), 86400, function () {
        return \App\Models\Visitor::when(auth()->user()?->company_id, function ($query, $companyId) {
            return $query->where('company_id', auth()->user()->company_id);
        })->count();
    });*/ 
@endphp

<div class="page-title-head d-flex align-items-center gap-2">
    <div class="flex-grow-1">
        <h4 class="fs-16 text-uppercase fw-bold mb-0">Dashboard</h4>
    </div>
</div>

<div class="row justify-content-center">
    @include('backend.includes.dashboard-card', [
        'name' => 'Pages',
        'icon' => 'ti ti-pencil',
        'count' => $pageCount,
        'url' => route('pages.index'),
    ])

    @include('backend.includes.dashboard-card', [
        'name' => 'Posts',
        'icon' => 'ti ti-article',
        'count' => $postCount,
        'url' => route('posts.index'),
    ])

    @include('backend.includes.dashboard-card', [
        'name' => 'Seo Meta',
        'icon' => 'ti ti-search',
        'count' => $seometaCount,
        'url' => route('seo-meta.index'),
    ])  
    
    @include('backend.includes.dashboard-card', [
        'name' => 'Form Submissions',
        'icon' => 'ti ti-message-question',
        'count' => $formCount,
        'url' => route('forms.by', ['form_name' => (auth()->user()->company_id == 1) ? 'admission' : 'contact']),
    ])

    @include('backend.includes.dashboard-card', [
        'name' => 'Menus',
        'icon' => 'ti ti-menu-2',
        'count' => $menuCount,
        'url' => route('backend.menus'),
    ])

    @include('backend.includes.dashboard-card', [
        'name' => 'Media Uploads',
        'icon' => 'ti ti-file-upload',
        'count' => $mediaCount,
        'url' => route('uploaded-files.index'),
    ])

    {{-- @include('backend.includes.dashboard-card', [
        'name' => 'Visitors',
        'icon' => 'ti ti-world',
        'count' => $visitors,
        'url' => route('visitors.index'),
    ]) --}}
   
</div>
@endsection
