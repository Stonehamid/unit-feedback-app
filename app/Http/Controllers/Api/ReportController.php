<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse; // Tambahkan ini untuk type hinting response

class ReportController extends Controller
{
    protected ReportService $reportService;

    // Gunakan dependency injection untuk service
    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Menampilkan daftar semua laporan (hanya untuk admin).
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Report::class);
        $reports = Report::with(['unit', 'admin'])->paginate(10);
        return response()->json($reports);
    }

    /**
     * Menyimpan laporan baru.
     */
    public function store(Request $request): JsonResponse
    {
        // 1. Validasi input dari user
        $validatedData = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        // 2. Ambil user yang sedang login
        $adminUser = $request->user();

        // 3. Panggil service dengan data yang sudah divalidasi dan objek user
        $report = $this->reportService->createReport($validatedData, $adminUser);

        // 4. Kembalikan response sukses
        return response()->json($report, 201);
    }

    /**
     * Menampilkan detail satu laporan.
     */
    public function show(Report $report): JsonResponse
    {
        // Policy untuk memastikan hanya admin yang bisa lihat (atau pembuat laporan)
        $this->authorize('view', $report); 
        $report->load(['unit', 'admin']);
        return response()->json($report);
    }

    /**
     * Memperbarui laporan.
     */
    public function update(Request $request, Report $report): JsonResponse
    {
        $this->authorize('update', $report);
        
        $validatedData = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
        ]);

        $report->update($validatedData);
        return response()->json($report);
    }

    /**
     * Menghapus laporan.
     */
    public function destroy(Report $report): JsonResponse
    {
        $this->authorize('delete', $report);
        $report->delete();
        return response()->json(null, 204);
    }
}