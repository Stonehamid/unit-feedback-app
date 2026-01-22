<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Http\Requests\Admin\Report\ReplyReportRequest;
use App\Services\Admin\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $reports = $this->reportService->getReports($request->all());

        return response()->json([
            'success' => true,
            'data' => $reports,
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $report = $this->reportService->getReportDetail($id);

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    public function reply(ReplyReportRequest $request, string $id): JsonResponse
    {
        $report = $this->reportService->replyToReport($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil ditanggapi',
            'data' => $report,
        ]);
    }

    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:baru,diproses,selesai,ditolak',
        ]);

        $report = $this->reportService->updateReportStatus($id, $validated['status']);

        return response()->json([
            'success' => true,
            'message' => 'Status laporan berhasil diperbarui',
            'data' => $report,
        ]);
    }

    public function updatePriority(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'priority' => 'required|in:rendah,sedang,tinggi,kritis',
        ]);

        $report = $this->reportService->updateReportPriority($id, $validated['priority']);

        return response()->json([
            'success' => true,
            'message' => 'Prioritas laporan berhasil diperbarui',
            'data' => $report,
        ]);
    }

    public function assign(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'admin_id' => 'required|exists:users,id',
        ]);

        $report = $this->reportService->assignReport($id, $validated['admin_id']);

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil ditugaskan',
            'data' => $report,
        ]);
    }

    public function stats(): JsonResponse
    {
        $stats = $this->reportService->getReportStats();

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}