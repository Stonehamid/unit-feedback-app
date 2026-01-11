<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        $units = Unit::with(['ratings', 'messages', 'reports'])->paginate(10);
        return response()->json($units);
    }

    public function store(StoreUnitRequest $request)
    {
        $unit = Unit::create($request->validated());
        return response()->json($unit, 201);
    }

    public function show(Unit $unit)
    {
        // Load relasi untuk detail view
        $unit->load(['ratings', 'messages', 'reports.admin']);
        return response()->json($unit);
    }

    public function update(UpdateUnitRequest $request, Unit $unit)
    {
        $unit->update($request->validated());
        return response()->json($unit);
    }

    public function destroy(Unit $unit)
    {
        $this->authorize('delete', $unit); // Otorisasi via Policy
        $unit->delete();
        return response()->json(null, 204);
    }
}