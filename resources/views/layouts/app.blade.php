<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Sistema de Encuestas'))</title>

    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="{{ asset('assets/css/sb-admin-2.min.css') }}" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
@php
    $user = auth()->user();
    $role = $user?->role;
@endphp
<body id="page-top">
    <div id="wrapper">
        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('dashboard') }}">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="sidebar-brand-text mx-3">{{ config('app.name', 'Sistema') }}</div>
            </a>

            <hr class="sidebar-divider my-0">

            <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('dashboard') }}">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span></a>
            </li>

            <hr class="sidebar-divider">

            <div class="sidebar-heading">
                Gestión
            </div>

            @if ($role === \App\Models\User::ROLE_ADMIN)
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-fw fa-users"></i>
                        <span>Usuarios</span></a>
                </li>
            @endif

            @if (in_array($role, [\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_TEACHER]))
                <li class="nav-item {{ request()->routeIs('quizzes.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('quizzes.index') }}">
                        <i class="fas fa-fw fa-file-alt"></i>
                        <span>Encuestas</span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('quizzes.index') }}#invitations">
                        <i class="fas fa-fw fa-key"></i>
                        <span>Códigos de Invitación</span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('quizzes.index') }}#analysis">
                        <i class="fas fa-fw fa-brain"></i>
                        <span>Análisis IA</span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseReports"
                        aria-expanded="true" aria-controls="collapseReports">
                        <i class="fas fa-fw fa-chart-area"></i>
                        <span>Reportes</span>
                    </a>
                    <div id="collapseReports" class="collapse {{ request()->routeIs('reports.*') ? 'show' : '' }}" aria-labelledby="headingReports" data-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <h6 class="collapse-header">Reportes:</h6>
                            <a class="collapse-item {{ request()->routeIs('reports.summary') ? 'active' : '' }}" href="{{ route('reports.summary') }}">Resumen</a>
                            <a class="collapse-item {{ request()->routeIs('reports.students') ? 'active' : '' }}" href="{{ route('reports.students') }}">Estudiantes</a>
                            <a class="collapse-item {{ request()->routeIs('reports.surveys') ? 'active' : '' }}" href="{{ route('reports.surveys') }}">Encuestas</a>
                        </div>
                    </div>
                </li>
            @endif

            @if ($role === \App\Models\User::ROLE_STUDENT)
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-fw fa-list-check"></i>
                        <span>Mis Encuestas</span></a>
                </li>
                <li class="nav-item {{ request()->routeIs('surveys.access.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('surveys.access.form') }}">
                        <i class="fas fa-fw fa-barcode"></i>
                        <span>Usar Código</span></a>
                </li>
            @endif

            <hr class="sidebar-divider d-none d-md-block">

            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <form class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                        <div class="input-group">
                            <input type="text" class="form-control bg-light border-0 small" placeholder="Buscar..." aria-label="Search" aria-describedby="basic-addon2" readonly>
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button" disabled>
                                    <i class="fas fa-search fa-sm"></i>
                                </button>
                            </div>
                        </div>
                    </form>

                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-bell fa-fw"></i>
                                <span class="badge badge-danger badge-counter">3+</span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="alertsDropdown">
                                <h6 class="dropdown-header">
                                    Alertas
                                </h6>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-primary">
                                            <i class="fas fa-file-alt text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500">Noviembre 8, 2025</div>
                                        <span class="font-weight-bold">Nueva encuesta disponible.</span>
                                    </div>
                                </a>
                                <a class="dropdown-item text-center small text-gray-500" href="#">Ver todas las alertas</a>
                            </div>
                        </li>

                        <div class="topbar-divider d-none d-sm-block"></div>

                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">{{ $user?->name }}</span>
                                <img class="img-profile rounded-circle" src="{{ asset('img/undraw_profile.svg') }}" alt="Avatar">
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Perfil
                                </a>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Configuración
                                </a>
                                <div class="dropdown-divider"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="dropdown-item" type="submit">
                                        <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                        Cerrar sesión
                                    </button>
                                </form>
                            </div>
                        </li>
                    </ul>
                </nav>
                <!-- End of Topbar -->

                <div class="container-fluid">
                    @if (session('status'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle mr-2"></i>{{ session('status') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>{{ __('Se encontraron algunos problemas:') }}</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @isset($header)
                        <div class="d-sm-flex align-items-center justify-content-between mb-4">
                            <h1 class="h3 mb-0 text-gray-800">{{ $header }}</h1>
                            @yield('header-actions')
                        </div>
                    @endisset

                    {{ $slot }}
                </div>
            </div>

            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; {{ config('app.name', 'Sistema de Encuestas') }} {{ now()->year }}</span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <div class="modal fade" id="globalLoaderModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body text-center py-5">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="sr-only">{{ __('Cargando...') }}</span>
                    </div>
                    <p class="mb-0 text-muted">{{ __('Procesando, por favor espera...') }}</p>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/jquery-easing/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('js/sb-admin-2.min.js') }}"></script>
    <script>
        (function ($) {
            $(document).on('submit', 'form.js-show-loader', function () {
                $('#globalLoaderModal').modal('show');
            });

            $(document).on('click', '.js-show-loader', function () {
                $('#globalLoaderModal').modal('show');
            });
        })(jQuery);
    </script>
    @stack('scripts-before-chart')
    <script src="{{ asset('vendor/chart.js/Chart.min.js') }}"></script>
    @stack('scripts')
</body>
</html>
