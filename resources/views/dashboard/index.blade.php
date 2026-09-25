@extends('layouts.dashboard', ['role' => $role, 'dashboardRoute' => $dashboardRoute])

@section('title', 'Dashboard ' . $role)

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">Dashboard {{ $role }}</h1>
            <p class="mb-0 text-gray-600">Selamat datang, {{ auth()->user()->name }}.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Akun aktif</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ auth()->user()->name }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-user-check fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Hak akses</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $role }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-user-shield fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header py-3"><h2 class="h6 m-0 font-weight-bold text-primary">Notaris App</h2></div>
        <div class="card-body">Anda masuk sebagai <strong>{{ $role }}</strong>. Menu dan akses aplikasi akan mengikuti role akun Anda.</div>
    </div>
@endsection
