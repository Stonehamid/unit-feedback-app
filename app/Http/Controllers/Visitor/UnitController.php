<?php

namespace App\Http\Controllers\Visitor;

use App\Http\Controllers\Controller;


use App\Http\Requests\Visitor\BrowseUnitsRequest;
use App\Services\Visitor\UnitBrowserService;
use Illuminate\Http\JsonResponse;

class UnitController extends Controller
{
    public function __construct(
        protected UnitBrowserService $unitService
    ) {}

    public function index(BrowseUnitsRequest $request): JsonResponse
    {
        $units = $this->unitService->browseUnits($request->validated());

        return response()->json([
            'success' => true,
            'data' => [
                'units' => $units,
                'total' => $units->total(),
                'per_page' => $units->perPage(),
                'current_page' => $units->currentPage(),
                'last_page' => $units->lastPage()
            ]
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $unit = $this->unitService->getUnitDetail($id);

        return response()->json([
            'success' => true,
            'data' => $unit
        ]);
    }

    public function search(string $query): JsonResponse
    {
        $units = $this->unitService->searchUnits($query);

        return response()->json([
            'success' => true,
            'data' => [
                'query' => $query,
                'results' => $units,
                'total' => $units->count()
            ]
        ]);
    }

    public function byType(string $type): JsonResponse
    {
        $units = $this->unitService->getUnitsByType($type);

        return response()->json([
            'success' => true,
            'data' => [
                'type' => $type,
                'units' => $units,
                'total' => $units->count()
            ]
        ]);
    }

    public function nearby(): JsonResponse
    {
        $latitude = request()->get('lat');
        $longitude = request()->get('lng');
        $radius = request()->get('radius', 5);

        $units = $this->unitService->getNearbyUnits($latitude, $longitude, $radius);

        return response()->json([
            'success' => true,
            'data' => [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'radius_km' => $radius,
                'units' => $units,
                'total' => $units->count()
            ]
        ]);
    }

    public function popular(): JsonResponse
    {
        $units = $this->unitService->getPopularUnits();

        return response()->json([
            'success' => true,
            'data' => [
                'units' => $units,
                'total' => $units->count()
            ]
        ]);
    }

    public function trackVisit(string $unitId): JsonResponse
    {
        $sessionId = request()->session()->getId();
        $visit = $this->unitService->trackUnitVisit($unitId, $sessionId);

        return response()->json([
            'success' => true,
            'message' => 'Kunjungan berhasil dicatat',
            'data' => [
                'visit_id' => $visit->id,
                'unit_id' => $visit->unit_id,
                'waktu_masuk' => $visit->waktu_masuk
            ]
        ], 201);
    }

    public function endVisit(string $visitId): JsonResponse
    {
        $visit = $this->unitService->endUnitVisit($visitId);

        return response()->json([
            'success' => true,
            'message' => 'Kunjungan berhasil diselesaikan',
            'data' => [
                'visit_id' => $visit->id,
                'durasi_detik' => $visit->durasi_detik,
                'durasi_menit' => $visit->durasi_menit
            ]
        ]);
    }
}