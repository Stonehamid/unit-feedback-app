<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Http\Requests\Admin\Empolyee\StoreEmployeeRequest;
use App\Http\Requests\Admin\Empolyee\UpdateEmployeeRequest;
use App\Services\Admin\UnitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function __construct(
        protected UnitService $unitService
    ) {}

    public function index(Request $request, string $unitId): JsonResponse
    {
        $employees = $this->unitService->getUnitEmployees($unitId, $request->all());

        return response()->json([
            'success' => true,
            'data' => $employees,
        ]);
    }

    public function store(StoreEmployeeRequest $request, string $unitId): JsonResponse
    {
        $employee = $this->unitService->createEmployee($unitId, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Pekerja berhasil ditambahkan',
            'data' => $employee,
        ], 201);
    }

    public function show(string $unitId, string $id): JsonResponse
    {
        $employee = $this->unitService->getEmployeeDetail($unitId, $id);

        return response()->json([
            'success' => true,
            'data' => $employee,
        ]);
    }

    public function update(UpdateEmployeeRequest $request, string $unitId, string $id): JsonResponse
    {
        $employee = $this->unitService->updateEmployee($unitId, $id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Data pekerja berhasil diperbarui',
            'data' => $employee,
        ]);
    }

    public function destroy(string $unitId, string $id): JsonResponse
    {
        $this->unitService->deleteEmployee($unitId, $id);

        return response()->json([
            'success' => true,
            'message' => 'Pekerja berhasil dihapus',
        ]);
    }

    public function updateStatus(Request $request, string $unitId, string $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:aktif,cuti,resign',
        ]);

        $employee = $this->unitService->updateEmployeeStatus($unitId, $id, $validated['status']);

        return response()->json([
            'success' => true,
            'message' => 'Status pekerja berhasil diperbarui',
            'data' => $employee,
        ]);
    }
}