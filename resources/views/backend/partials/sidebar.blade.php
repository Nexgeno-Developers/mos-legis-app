<div class="sidenav-menu">

<!-- Brand Logo -->
<a href="" class="logo">
    <span class="logo-light">
        <span class="logo-lg"><img src="{{ backend_logo_url() }}" alt="logo"></span>
        <span class="logo-sm"><img src="{{ backend_logo_url() }}" alt="small logo"></span>
    </span>

    <span class="logo-dark">
        <span class="logo-lg"><img src="{{ backend_logo_url() }}" alt="dark logo"></span>
        <span class="logo-sm"><img src="{{ backend_logo_url() }}" alt="small logo"></span>
    </span>
</a>

<!-- Sidebar Hover Menu Toggle Button -->
<button class="button-sm-hover">
    <i class="ti ti-circle align-middle"></i>
</button>

<!-- Full Sidebar Menu Close Button -->
<button class="button-close-fullsidebar">
    <i class="ti ti-x align-middle"></i>
</button>

<div data-simplebar>

    <!--- Sidenav Menu -->
    <ul class="side-nav">
        <li class="side-nav-title">Navigation</li>

        <li class="side-nav-item">
            <a href="{{route('backend.dashboard')}}" class="side-nav-link">
                <span class="menu-icon"><i class="ti ti-dashboard"></i></span>
                <span class="menu-text"> Dashboard </span>
            </a>
        </li> 

        <li class="side-nav-item">
            <a href="{{ route('companies.edit', 1) }}" class="side-nav-link">
                <span class="menu-icon"><i class="ti ti-school"></i></span>
                <span class="menu-text"> Company </span>
            </a>
        </li>        
        
        <li class="side-nav-item">
            <a href="{{ route('pages.index') }}" class="side-nav-link">
                <span class="menu-icon"><i class="ti ti-pencil"></i></span>
                <span class="menu-text"> Pages </span>
            </a>
        </li> 

        <li class="side-nav-item">
            <a data-bs-toggle="collapse" href="#sidebarPosts" aria-expanded="false" aria-controls="sidebarPosts"
                class="side-nav-link">
                <span class="menu-icon"><i class="ti ti-article"></i></span>
                <span class="menu-text"> Posts </span>
                <span class="menu-arrow"></span>
            </a>
            <div class="collapse" id="sidebarPosts">
                <ul class="sub-menu">
                    <li class="side-nav-item">
                        <a href="{{ route('posts.index') }}" class="side-nav-link">
                            <span class="menu-text">Posts</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="{{ route('post-categories.index') }}" class="side-nav-link">
                            <span class="menu-text">Categories</span>
                        </a>
                    </li>
                    {{-- <li class="side-nav-item">
                        <a href="{{ route('post-tags.index') }}" class="side-nav-link">
                            <span class="menu-text">Tags</span>
                        </a>
                    </li> --}}
                    <li class="side-nav-item">
                        <a href="{{ route('authors.index') }}" class="side-nav-link">
                            <span class="menu-text">Authors</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>
        
        @can('seo-meta view')
        <li class="side-nav-item">
            <a href="{{ route('seo-meta.index') }}" class="side-nav-link">
                <span class="menu-icon"><i class="ti ti-search"></i></span>
                <span class="menu-text"> SEO Meta </span>
            </a>
        </li>
        @endcan

        @can('seo-settings view')
        <li class="side-nav-item">
            <a href="{{ route('seo-settings.index') }}" class="side-nav-link">
                <span class="menu-icon"><i class="ti ti-settings"></i></span>
                <span class="menu-text"> SEO Settings </span>
            </a>
        </li>
        @endcan
        
        <li class="side-nav-item">
            <a href="{{ route('forms.by', ['form_name' => 'contact']) }}" class="side-nav-link">
                <span class="menu-icon"><i class="ti ti-message-question"></i></span>
                <span class="menu-text"> Form Submissions </span>
            </a>
        </li> 

        <li class="side-nav-item">
            <a data-bs-toggle="collapse" href="#sidebarUploads" aria-expanded="false" aria-controls="sidebarTables"
                class="side-nav-link">
                <span class="menu-icon"><i class="ti ti-file-upload"></i></span>
                <span class="menu-text"> Media Uploads </span>
                <span class="menu-arrow"></span>
            </a>
            <div class="collapse" id="sidebarUploads">
                <ul class="sub-menu">
                    <li class="side-nav-item">
                        <a href="{{ route('uploaded-files.create') }}" class="side-nav-link">
                            <span class="menu-text">Add New</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="{{ route('uploaded-files.index') }}" class="side-nav-link">
                            <span class="menu-text">All Uploads</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>        
          

        <li class="side-nav-item">
            <a href="{{ route('backend.menus') }}" class="side-nav-link">
                <span class="menu-icon"><i class="ti ti-menu"></i></span>
                <span class="menu-text"> Menus </span>
            </a>
        </li>        

        {{--<li class="side-nav-item">
            <a href="{{ route('visitors.index') }}" class="side-nav-link">
                <span class="menu-icon"><i class="ti ti-world"></i></span>
                <span class="menu-text"> Visitors </span>
            </a>
        </li>--}} 
        
        {{--<li class="side-nav-item">
            <a data-bs-toggle="collapse" href="#sidebarUsers" aria-expanded="false" aria-controls="sidebarUsers"
                class="side-nav-link">
                <span class="menu-icon"><i class="ti ti-users"></i></span>
                <span class="menu-text"> Users </span>
                <span class="menu-arrow"></span>
            </a>
            <div class="collapse" id="sidebarUsers">
                <ul class="sub-menu">
                    <li class="side-nav-item">
                        <a href="{{ route('users.index') }}" class="side-nav-link">
                            <span class="menu-text">Staffs</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="{{ route('roles.index') }}" class="side-nav-link">
                            <span class="menu-text">Roles</span>
                        </a>
                    </li>   
                </ul>
            </div>
        </li>--}}        

        <li class="side-nav-item">
            <a target="_blank" href="{{ url('') . '/command/optimize-clear?back=true' }}" class="side-nav-link text-danger fw-bold">
                <span class="menu-icon"><i class="ti ti-refresh"></i></span>
                <span class="menu-text"> CMS Clear Cache </span>
            </a>
        </li>

        <li class="side-nav-item">
            <a href="javascript:void(0)" id="frontend-cache-clear" class="side-nav-link text-warning fw-bold" data-url="{{ route('backend.frontend-cache-clear') }}">
                <span class="menu-icon"><i class="ti ti-world"></i></span>
                <span class="menu-text"> Website Cache Clear </span>
            </a>
        </li>

        <li class="side-nav-item">
            <a href="javascript:void(0)" id="frontend-sitemap-generate" class="side-nav-link text-success fw-bold" data-url="{{ route('backend.frontend-sitemap-generate') }}">
                <span class="menu-icon"><i class="ti ti-sitemap"></i></span>
                <span class="menu-text"> Generate Sitemap </span>
            </a>
        </li>

        <li class="side-nav-item">
            <a href="javascript:void(0)" id="frontend-robots-generate" class="side-nav-link text-success fw-bold" data-url="{{ route('backend.frontend-robots-generate') }}">
                <span class="menu-icon"><i class="ti ti-file-text"></i></span>
                <span class="menu-text"> Generate Robots.txt </span>
            </a>
        </li>
       

    </ul>
    <div class="clearfix"></div>
</div>
</div>

<script>
    (function() {
        const link = document.getElementById('frontend-cache-clear');
        if (!link) return;

        link.addEventListener('click', function() {
            const url = link.getAttribute('data-url');
            if (!url) {
                toastr.error('Frontend cache URL is not configured.');
                return;
            }

            if (link.classList.contains('disabled')) return;
            link.classList.add('disabled');

            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            $.ajax({
                url: url,
                method: 'POST',
                dataType: 'json',
                headers: csrf ? { 'X-CSRF-TOKEN': csrf } : {},
                timeout: 15000,
                success: function(response) {
                    if (response && response.ok && response.cleared) {
                        const at = response.at ? ` at ${response.at}` : '';
                        toastr.success(`Frontend cache cleared${at}`);
                    } else {
                        const message = response && response.message ? response.message : 'Frontend cache clear failed.';
                        toastr.error(message);
                    }
                },
                error: function(xhr) {
                    let message = 'Frontend cache clear failed.';
                    if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    toastr.error(message);
                },
                complete: function() {
                    link.classList.remove('disabled');
                }
            });
        });
    })();
</script>

<script>
    (function() {
        const link = document.getElementById('frontend-sitemap-generate');
        if (!link) return;

        link.addEventListener('click', function() {
            const url = link.getAttribute('data-url');
            if (!url) {
                toastr.error('Frontend sitemap URL is not configured.');
                return;
            }

            if (link.classList.contains('disabled')) return;
            link.classList.add('disabled');

            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            $.ajax({
                url: url,
                method: 'POST',
                dataType: 'json',
                headers: csrf ? { 'X-CSRF-TOKEN': csrf } : {},
                timeout: 30000,
                success: function(response) {
                    if (response && response.ok) {
                        const urlCount = typeof response.urlCount === 'number' ? response.urlCount : null;
                        const written = response.written ? ` (${response.written})` : '';
                        const countText = urlCount !== null ? ` with ${urlCount} URLs` : '';
                        toastr.success(`Sitemap generated${countText}${written}`);
                    } else {
                        const message = response && response.message ? response.message : 'Sitemap generation failed.';
                        toastr.error(message);
                    }
                },
                error: function(xhr) {
                    let message = 'Sitemap generation failed.';
                    if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    toastr.error(message);
                },
                complete: function() {
                    link.classList.remove('disabled');
                }
            });
        });
    })();
</script>

<script>
    (function() {
        const link = document.getElementById('frontend-robots-generate');
        if (!link) return;

        link.addEventListener('click', function() {
            const url = link.getAttribute('data-url');
            if (!url) {
                toastr.error('Frontend robots URL is not configured.');
                return;
            }

            if (link.classList.contains('disabled')) return;
            link.classList.add('disabled');

            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            $.ajax({
                url: url,
                method: 'POST',
                dataType: 'json',
                headers: csrf ? { 'X-CSRF-TOKEN': csrf } : {},
                timeout: 30000,
                success: function(response) {
                    if (response && response.ok) {
                        toastr.success('Robots.txt generated');
                    } else {
                        const message = response && response.message ? response.message : 'Robots generation failed.';
                        toastr.error(message);
                    }
                },
                error: function(xhr) {
                    let message = 'Robots generation failed.';
                    if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    toastr.error(message);
                },
                complete: function() {
                    link.classList.remove('disabled');
                }
            });
        });
    })();
</script>
