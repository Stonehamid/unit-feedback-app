<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\DashboardService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    public function stats(): JsonResponse
    {
        $stats = $this->dashboardService->getDashboardStats();

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    public function charts(): JsonResponse
    {
        $charts = $this->dashboardService->getChartData();

        return response()->json([
            'success' => true,
            'data' => $charts,
        ]);
    }

    public function overview(): JsonResponse
    {
        $overview = $this->dashboardService->getOverviewData();

        return response()->json([
            'success' => true,
            'data' => $overview,
        ]);
    }
}