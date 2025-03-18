<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', '発注サイト')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Hiragino Kaku Gothic Pro', 'Meiryo', sans-serif;
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.3rem;
        }
        
        .main-content {
            flex: 1;
            padding: 2rem 0;
        }
        
        .card {
            border-radius: 10px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0, 0, 0, 0.125);
            font-weight: 600;
        }
        
        .btn-primary {
            background-color: #4285F4;
            border-color: #4285F4;
        }
        
        .btn-primary:hover {
            background-color: #3367D6;
            border-color: #3367D6;
        }
        
        .btn-success {
            background-color: #34A853;
            border-color: #34A853;
        }
        
        .btn-success:hover {
            background-color: #2E7D32;
            border-color: #2E7D32;
        }
        
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        
        .footer {
            background-color: #fff;
            border-top: 1px solid #e9ecef;
            padding: 1rem 0;
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        /* サイドバー */
        .sidebar {
            position: fixed;
            top: 56px;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 48px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
            background-color: #fff;
        }
        
        .sidebar-sticky {
            position: relative;
            top: 0;
            height: calc(100vh - 48px);
            padding-top: .5rem;
            overflow-x: hidden;
            overflow-y: auto;
        }
        
        .sidebar .nav-link {
            font-weight: 500;
            color: #333;
            padding: 0.5rem 1rem;
        }
        
        .sidebar .nav-link.active {
            color: #4285F4;
        }
        
        .sidebar .nav-link:hover {
            color: #3367D6;
        }
        
        .sidebar .nav-link i {
            margin-right: 0.5rem;
        }
        
        @media (max-width: 767.98px) {
            .sidebar {
                position: static;
                padding-top: 0;
                box-shadow: none;
            }
            
            .sidebar-sticky {
                height: auto;
            }
        }
    </style>
    @yield('styles')
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-md navbar-dark bg-dark fixed-top">
            <div class="container-fluid">
                <a class="navbar-brand" href="{{ auth()->guard('admin')->check() ? route('admin.dashboard') : route('store.dashboard') }}">
                    発注サイト
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarCollapse">
                    <ul class="navbar-nav me-auto mb-2 mb-md-0">
                        @auth('admin')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">ダッシュボード</a>
                            </li>
                        @endauth
                        
                        @auth('store')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('store.dashboard') ? 'active' : '' }}" href="{{ route('store.dashboard') }}">発注入力</a>
                            </li>
                        @endauth
                    </ul>
                    
                    <ul class="navbar-nav ms-auto mb-2 mb-md-0">
                        @auth('admin')
                            <li class="nav-item">
                                <span class="nav-link">{{ auth()->guard('admin')->user()->admin_name }} さん</span>
                            </li>
                        @endauth
                        
                        @auth('store')
                            <li class="nav-item">
                                <span class="nav-link">{{ auth()->guard('store')->user()->store_name }} さん</span>
                            </li>
                        @endauth
                        
                        <li class="nav-item">
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-link nav-link">ログアウト</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    
    <div class="container-fluid mt-5 pt-3">
        <div class="row">
            @hasSection('sidebar')
                <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                    <div class="sidebar-sticky">
                        @yield('sidebar')
                    </div>
                </div>
                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    
                    @yield('content')
                </main>
            @else
                <main class="col-12 main-content">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    
                    @yield('content')
                </main>
            @endif
        </div>
    </div>
    
    <footer class="footer mt-auto">
        <div class="container">
            <div class="text-center">
                <p>&copy; {{ date('Y') }} 発注サイト - All Rights Reserved.</p>
            </div>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.3/dist/jquery.min.js"></script>
    @yield('scripts')
</body>
</html> 