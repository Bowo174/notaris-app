<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | Notaris App</title>
    <link href="{{ asset('template_dashboard/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="{{ asset('template_dashboard/css/sb-admin-2.min.css') }}" rel="stylesheet">
    <style>
        .bg-gradient-primary { background-color: #174a45; background-image: linear-gradient(180deg, #205b54 10%, #123d39 100%); }
        .text-primary { color: #174a45 !important; }
        .border-left-primary { border-left-color: #174a45 !important; }
        .btn-primary { background-color: #174a45; border-color: #174a45; }
        .btn-primary:hover { background-color: #123d39; border-color: #123d39; }
        .sidebar-brand-text { font-size: .92rem; }
    </style>
    @stack('styles')
</head>
<body id="page-top">
    <div id="wrapper">
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route($dashboardRoute) }}">
                <span class="sidebar-brand-icon"><i class="fas fa-balance-scale" aria-hidden="true"></i></span>
                <span class="sidebar-brand-text mx-2">Notaris App</span>
            </a>
            <hr class="sidebar-divider my-0">
            <li class="nav-item {{ request()->routeIs($dashboardRoute) ? 'active' : '' }}">
                <a class="nav-link" href="{{ route($dashboardRoute) }}"><i class="fas fa-fw fa-tachometer-alt"></i><span>Dashboard</span></a>
            </li>
            @if ($role === 'Admin')
                <li class="nav-item {{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.services.index') }}"><i class="fas fa-fw fa-concierge-bell"></i><span>Layanan</span></a>
                </li>
            @endif
            <hr class="sidebar-divider">
            <div class="sidebar-heading">Akun</div>
            <li class="nav-item">
                <span class="nav-link"><i class="fas fa-fw fa-user-shield"></i><span>{{ $role }}</span></span>
            </li>
            <hr class="sidebar-divider d-none d-md-block">
            <div class="text-center d-none d-md-inline"><button class="rounded-circle border-0" id="sidebarToggle" aria-label="Tutup menu"></button></div>
        </ul>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow-sm">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3" aria-label="Buka menu"><i class="fa fa-bars"></i></button>
                    <ul class="navbar-nav ml-auto align-items-center">
                        <li class="nav-item mr-3 d-none d-sm-inline text-gray-600 small">{{ $role }}</li>
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">{{ auth()->user()->name }}</span>
                                <img class="img-profile rounded-circle" src="{{ asset('template_dashboard/img/undraw_profile.svg') }}" alt="">
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                                <div class="dropdown-item-text small text-gray-600">{{ auth()->user()->email }}</div>
                                <div class="dropdown-divider"></div>
                                <form method="POST" action="{{ route('logout') }}">@csrf
                                    <button class="dropdown-item" type="submit"><i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>Keluar</button>
                                </form>
                            </div>
                        </li>
                    </ul>
                </nav>
                <main class="container-fluid">
                    @yield('content')
                </main>
            </div>
            <footer class="sticky-footer bg-white"><div class="container my-auto"><div class="copyright text-center my-auto"><span>Notaris App</span></div></div></footer>
        </div>
    </div>
    <a class="scroll-to-top rounded" href="#page-top" aria-label="Kembali ke atas"><i class="fas fa-angle-up"></i></a>
    <script src="{{ asset('template_dashboard/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('template_dashboard/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('template_dashboard/vendor/jquery-easing/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('template_dashboard/js/sb-admin-2.min.js') }}"></script>
    @stack('scripts')
</body>
</html>
