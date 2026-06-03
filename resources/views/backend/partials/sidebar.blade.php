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
            <a href="{{ route('seo-meta.index') }}" class="side-nav-link">
                <span class="menu-icon"><i class="ti ti-search"></i></span>
                <span class="menu-text"> SEO Meta </span>
            </a>
        </li>
        
        <!-- <li class="side-nav-item">
            <a href="{{ route('forms.by', ['form_name' => 'contact']) }}" class="side-nav-link">
                <span class="menu-icon"><i class="ti ti-message-question"></i></span>
                <span class="menu-text"> Form Submissions </span>
            </a>
        </li>-->

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

        <li class="side-nav-item">
            <a href="{{ route('visitors.index') }}" class="side-nav-link">
                <span class="menu-icon"><i class="ti ti-world"></i></span>
                <span class="menu-text"> Visitors </span>
            </a>
        </li> 
        
        <li class="side-nav-item">
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
        </li>       

        <li class="side-nav-item">
            <a target="_blank" href="{{ url('') . '/command/optimize-clear?back=true' }}" class="side-nav-link text-danger fw-bold">
                <span class="menu-icon"><i class="ti ti-refresh"></i></span>
                <span class="menu-text">Clear Cache </span>
            </a>
        </li>
    </ul>
    <div class="clearfix"></div>
</div>
</div>
