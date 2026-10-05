<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
    ) {}

    public function index(Request $request): View
    {
        $admin = $request->user('admin');

        abort_unless($admin?->can('dashboard_view'), 403);

        return view('backoffice.admin.dashboard', array_merge(
            [
                'title' => 'Admin Dashboard',
                'sub_title' => 'Live operational overview',
                'admin' => $admin,
                'breadcrumb' => [
                    ['text' => 'Dashboard', 'url' => null],
                ],
            ],
            $this->dashboardService->build($admin),
        ));
    }
}
