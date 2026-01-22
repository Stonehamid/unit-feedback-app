<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Export\PdfExportService;
use App\Services\Export\ExcelExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends Controller
{
    public function __construct(
        protected PdfExportService $pdfService,
        protected ExcelExportService $excelService
    ) {}

    public function exportRatingsPdf(Request $request): BinaryFileResponse
    {
        $filters = $request->all();
        $filePath = $this->pdfService->exportRatings($filters);

        return response()->download($filePath, 'ratings-report-' . date('Y-m-d') . '.pdf');
    }

    public function exportRatingsExcel(Request $request): BinaryFileResponse
    {
        $filters = $request->all();
        $filePath = $this->excelService->exportRatings($filters);

        return response()->download($filePath, 'ratings-report-' . date('Y-m-d') . '.xlsx');
    }

    public function exportUnitsPdf(Request $request): BinaryFileResponse
    {
        $filters = $request->all();
        $filePath = $this->pdfService->exportUnits($filters);

        return response()->download($filePath, 'units-report-' . date('Y-m-d') . '.pdf');
    }

    public function exportUnitsExcel(Request $request): BinaryFileResponse
    {
        $filters = $request->all();
        $filePath = $this->excelService->exportUnits($filters);

        return response()->download($filePath, 'units-report-' . date('Y-m-d') . '.xlsx');
    }

    public function exportVisitsPdf(Request $request): BinaryFileResponse
    {
        $filters = $request->all();
        $filePath = $this->pdfService->exportVisits($filters);

        return response()->download($filePath, 'visits-report-' . date('Y-m-d') . '.pdf');
    }

    public function exportVisitsExcel(Request $request): BinaryFileResponse
    {
        $filters = $request->all();
        $filePath = $this->excelService->exportVisits($filters);

        return response()->download($filePath, 'visits-report-' . date('Y-m-d') . '.xlsx');
    }

    public function exportReportsPdf(Request $request): BinaryFileResponse
    {
        $filters = $request->all();
        $filePath = $this->pdfService->exportReports($filters);

        return response()->download($filePath, 'reports-report-' . date('Y-m-d') . '.pdf');
    }

    public function exportReportsExcel(Request $request): BinaryFileResponse
    {
        $filters = $request->all();
        $filePath = $this->excelService->exportReports($filters);

        return response()->download($filePath, 'reports-report-' . date('Y-m-d') . '.xlsx');
    }

    public function exportDashboardStats(Request $request): JsonResponse
    {
        $filters = $request->all();
        $exportData = $this->pdfService->exportDashboardStats($filters);

        return response()->json([
            'success' => true,
            'data' => $exportData,
        ]);
    }
}