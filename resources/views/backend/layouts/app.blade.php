<!DOCTYPE html>
<html lang="en">
    
    <head>
        <meta charset="utf-8" />
        <title>Backend | {{ isset($moduleName) ? $moduleName : 'Page' }}</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
        <meta content="Coderthemes" name="author" />

        <!-- App favicon -->
        <link rel="shortcut icon" href="{{ asset('backend/img/favicon.ico') }}">

        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="app-url" content="{{ url('/backend') }}">
        <meta name="file-base-url" content="{{ asset('') }}">        
        <meta name="front-file-base-url" content="{{ config('custom.assets_url') }}">        

        @include('backend.partials.css')
        @include('backend.partials.js')
    </head>

    <body>
        <!-- Begin page -->
        <div class="wrapper">

            <!-- Sidenav Menu Start -->
            @include('backend.partials.sidebar')
            <!-- Sidenav Menu End -->
            
            <!-- Topbar Start -->
            @include('backend.partials.header')
            <!-- Topbar End -->

            <!-- ============================================================== -->
            <!-- Start Page Content here -->
            <!-- ============================================================== -->
            <div class="page-content">
                <div class="page-container">
                    @yield('content')
                </div>
                <!-- container -->

                <!-- modals -->
                @include('backend.includes.modal')

                <!-- Footer Start -->
                @include('backend.partials.footer')
                <!-- end Footer -->
            </div>
            <!-- ============================================================== -->
            <!-- End Page content -->
            <!-- ============================================================== -->
        </div>
        <!-- END wrapper -->

        <!-- Theme Settings Popup Start -->
        @include('backend.partials.theme_setting')
        <!-- Theme Settings Popup End-->
    </body>
</html>