<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;
use App\Services\Unit\UnitFilterService;
use App\Services\Unit\UnitService;
use App\Services\Unit\UnitStatisticsService;
use App\Services\Unit\UnitBulkActionService;
use App\Services\Unit\UnitExportService;

class UnitController extends Controller
{
    protected $filterService;
    protected $unitService;
    protected $statsService;
    protected $bulkService;
    protected $exportService;
    
    public function __construct(
        UnitFilterService $filterService,
        UnitService $unitService,
        UnitStatisticsService $statsService,
        UnitBulkActionService $bulkService,
        UnitExportService $exportService = null
    ) {
        $this->filterService = $filterService;
        $this->unitService = $unitService;
        $this->statsService = $statsService;
        $this->bulkService = $bulkService;
        $this->exportService = $exportService;
    }
    
    public function index(Request $request)
    {
        $query = $this->filterService->buildQuery($request);
        $units = $this->filterService->getPagination($query, $request);
        $filterOptions = $this->filterService->getFilterOptions();
        
        return [
            'units' => $units,
            'filters' => [
                'options' => $filterOptions,
                'applied' => $request->all(),
            ],
            'stats' => $this->statsService->getOverallStats(),
        ];
    }
    
    public function show(Unit $unit)
    {
        return $this->unitService->getUnitWithDetails($unit);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:units,name',
            'officer_name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'location' => 'required|string|max:255',
            'photo' => 'nullable|image|max:5120|mimes:jpg,jpeg,png,webp',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:20',
            'working_hours' => 'nullable|string|max:255',
        ]);
        
        $photo = $request->hasFile('photo') ? $request->file('photo') : null;
        $unit = $this->unitService->createUnit($validated, $photo);
        
        return [
            'unit' => $unit,
            'message' => 'Unit created successfully'
        ];
    }
    
    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255|unique:units,name,' . $unit->id,
            'officer_name' => 'sometimes|string|max:255',
            'type' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|min:10',
            'location' => 'sometimes|string|max:255',
            'photo' => 'nullable|image|max:5120|mimes:jpg,jpeg,png,webp',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:20',
            'working_hours' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
            'featured' => 'sometimes|boolean',
        ]);
        
        $photo = $request->hasFile('photo') ? $request->file('photo') : null;
        $removePhoto = $request->has('remove_photo') && $request->remove_photo === true;
        
        $updatedUnit = $this->unitService->updateUnit($unit, $validated, $photo, $removePhoto);
        
        return [
            'unit' => $updatedUnit,
            'message' => 'Unit updated successfully'
        ];
    }
    
    public function destroy(Unit $unit)
    {
        $force = request('action') === 'force';
        $result = $this->unitService->deleteUnit($unit, $force);
        
        if (!$result['success']) {
            return response()->json($result, 422);
        }
        
        return [
            'message' => $result['message']
        ];
    }
    
    public function statistics(Unit $unit)
    {
        return $this->statsService->getUnitStatistics($unit);
    }
    
    public function bulkAction(Request $request)
    {
        $request->validate([
            'unit_ids' => 'required|array',
            'unit_ids.*' => 'exists:units,id',
            'action' => 'required|in:activate,deactivate,feature,unfeature',
        ]);
        
        $result = $this->bulkService->handleBulkAction(
            $request->unit_ids, 
            $request->action
        );
        
        return [
            'message' => $result['message']
        ];
    }
    
    public function export(Request $request)
    {
        if (!$this->exportService) {
            $this->exportService = app(UnitExportService::class);
        }
        
        return $this->exportService->exportUnits($request->get('format', 'json'));
    }
}