<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        if ($request->user()->hasRole('Admin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($request->user()->hasRole('Staff')) {
            return redirect()->route('staff.dashboard');
        }

        abort(403);
    }

    public function admin(): View
    {
        return view('dashboard.index', [
            'role' => 'Admin',
            'dashboardRoute' => 'admin.dashboard',
        ]);
    }

    public function staff(): View
    {
        return view('dashboard.index', [
            'role' => 'Staff',
            'dashboardRoute' => 'staff.dashboard',
        ]);
    }
}
