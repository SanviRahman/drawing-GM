<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $admin = $request->user('admin');


         $breadcrumb =  [
                ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
            ];

        return view('backoffice.admin.dashboard', [
            'title' => 'Admin Dashboard',
            'admin' => $admin,
            'breadcrumb' => $breadcrumb,
        ]);
    }
}
