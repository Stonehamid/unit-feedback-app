<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    /**
     * Display listing of reports
     */
    public function index()
    {
        $reports = Report::with(['admin', 'unit'])
                        ->latest()
                        ->paginate(10);

        return $reports;
    }

    /**
     * Store new report
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $validated['admin_id'] = Auth::id();

        $report = Report::create($validated);

        return [
            'report' => $report->load('unit'),
            'message' => 'Report created successfully'
        ];
    }

    /**
     * Display single report
     */
    public function show(Report $report)
    {
        $report->load(['admin', 'unit']);
        
        return $report;
    }

    /**
     * Update report
     */
    public function update(Request $request, Report $report)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
        ]);

        $report->update($validated);

        return [
            'report' => $report,
            'message' => 'Report updated successfully'
        ];
    }

    /**
     * Delete report
     */
    public function destroy(Report $report)
    {
        $report->delete();

        return [
            'message' => 'Report deleted successfully'
        ];
    }
}