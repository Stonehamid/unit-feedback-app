<?php

namespace App\Http\Controllers\Visitor;

use App\Http\Controllers\Controller;

use App\Http\Requests\Visitor\StoreReportRequest;
use App\Services\Visitor\ReportService;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function store(StoreReportRequest $request): JsonResponse
    {
        $report = $this->reportService->storeReport(
            unitId: $request->validated('unit_id'),
            title: $request->validated('judul'),
            description: $request->validated('deskripsi'),
            type: $request->validated('tipe'),
            priority: $request->validated('prioritas', 'sedang'),
            attachments: $request->validated('lampiran', []),
            sessionId: $request->session()->getId(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil dikirim. Terima kasih atas kontribusi Anda.',
            'data' => [
                'report_id' => $report->id,
                'unit_id' => $report->unit_id,
                'status' => $report->status
            ]
        ], 201);
    }

    public function checkStatus(string $reportId): JsonResponse
    {
        $report = $this->reportService->getReportStatus($reportId);

        return response()->json([
            'success' => true,
            'data' => [
                'report_id' => $report->id,
                'status' => $report->status,
                'admin_response' => $report->tanggapan_admin,
                'responded_at' => $report->ditanggapi_pada
            ]
        ]);
    }

    public function myReports(): JsonResponse
    {
        $sessionId = request()->session()->getId();
        $reports = $this->reportService->getVisitorReports($sessionId);

        return response()->json([
            'success' => true,
            'data' => [
                'reports' => $reports,
                'total' => $reports->count()
            ]
        ]);
    }

    public function getReportTypes(): JsonResponse
    {
        $types = $this->reportService->getReportTypes();

        return response()->json([
            'success' => true,
            'data' => $types
        ]);
    }

    public function getPriorityLevels(): JsonResponse
    {
        $priorities = $this->reportService->getPriorityLevels();

        return response()->json([
            'success' => true,
            'data' => $priorities
        ]);
    }
}