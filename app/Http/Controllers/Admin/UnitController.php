<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Http\Requests\Admin\Unit\StoreUnitRequest;
use App\Http\Requests\Admin\Unit\UpdateUnitRequest;
use App\Models\Unit;
use App\Services\Admin\UnitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function __construct(
        protected UnitService $unitService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $units = $this->unitService->getUnits($request->all());

        return response()->json([
            'success' => true,
            'data' => $units,
        ]);
    }

    public function store(StoreUnitRequest $request): JsonResponse
    {
        $unit = $this->unitService->createUnit($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Unit berhasil dibuat',
            'data' => $unit,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $unit = $this->unitService->getUnitDetail($id);

        return response()->json([
            'success' => true,
            'data' => $unit,
        ]);
    }

    public function update(UpdateUnitRequest $request, string $id): JsonResponse
    {
        $unit = $this->unitService->updateUnit($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Unit berhasil diperbarui',
            'data' => $unit,
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $this->unitService->deleteUnit($id);

        return response()->json([
            'success' => true,
            'message' => 'Unit berhasil dihapus',
        ]);
    }

    public function toggleStatus(string $id): JsonResponse
    {
        $unit = $this->unitService->toggleUnitStatus($id);

        return response()->json([
            'success' => true,
            'message' => 'Status unit berhasil diubah',
            'data' => $unit,
        ]);
    }

    public function categories(string $id): JsonResponse
    {
        $categories = $this->unitService->getUnitCategories($id);

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }
}