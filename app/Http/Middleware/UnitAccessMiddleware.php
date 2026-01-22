<?php

namespace App\Http\Middleware;

use App\Models\Unit;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UnitAccessMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $unitId = $request->route('unit');

        if (!$unitId) {
            return $next($request);
        }

        $unit = Unit::find($unitId);

        if (!$unit) {
            return response()->json([
                'success' => false,
                'message' => 'Unit tidak ditemukan.',
            ], 404);
        }

        if (!$unit->status_aktif) {
            return response()->json([
                'success' => false,
                'message' => 'Unit sedang tidak aktif.',
                'unit' => [
                    'id' => $unit->id,
                    'nama' => $unit->nama_unit,
                    'status' => 'non_aktif',
                ]
            ], 403);
        }

        $request->merge(['unit' => $unit]);

        return $next($request);
    }
}