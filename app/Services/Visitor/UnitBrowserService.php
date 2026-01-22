<?php

namespace App\Services\Visitor;

use App\Models\Unit;
use App\Models\UnitVisit;
use App\Models\VisitorSession;
use Carbon\Carbon;

class UnitBrowserService
{
    public function browseUnits(array $filters = [])
    {
        $query = Unit::query()->aktif();
        
        if (isset($filters['jenis'])) {
            $query->where('jenis_unit', $filters['jenis']);
        }
        
        if (isset($filters['gedung'])) {
            $query->where('gedung', $filters['gedung']);
        }
        
        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('nama_unit', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('deskripsi', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('lokasi', 'like', '%' . $filters['search'] . '%');
            });
        }
        
        $sort = $filters['sort'] ?? 'nama_unit';
        $order = $filters['order'] ?? 'asc';
        
        return $query->orderBy($sort, $order)->paginate($filters['per_page'] ?? 15);
    }
    
    public function getUnitDetail(string $unitId)
    {
        $unit = Unit::aktif()->with(['ratingCategories' => function ($query) {
            $query->where('status_aktif', true)->orderBy('urutan');
        }])->findOrFail($unitId);
        
        $averageRatings = $unit->ratings()
            ->with('scores')
            ->get()
            ->map(function ($rating) {
                return $rating->rata_rata;
            })
            ->filter()
            ->avg();
        
        return [
            'unit' => $unit,
            'stats' => [
                'total_ratings' => $unit->ratings()->count(),
                'average_rating' => round($averageRatings, 1),
                'total_visits_today' => $unit->visits()->whereDate('tanggal', Carbon::today())->count(),
                'total_employees' => $unit->employees()->aktif()->count()
            ],
            'rating_categories' => $unit->ratingCategories,
            'operational_hours' => [
                'buka' => $unit->jam_buka ? Carbon::parse($unit->jam_buka)->format('H:i') : null,
                'tutup' => $unit->jam_tutup ? Carbon::parse($unit->jam_tutup)->format('H:i') : null
            ]
        ];
    }
    
    public function searchUnits(string $query)
    {
        return Unit::aktif()
            ->where(function ($q) use ($query) {
                $q->where('nama_unit', 'like', '%' . $query . '%')
                  ->orWhere('deskripsi', 'like', '%' . $query . '%')
                  ->orWhere('lokasi', 'like', '%' . $query . '%')
                  ->orWhere('kode_unit', 'like', '%' . $query . '%');
            })
            ->limit(20)
            ->get();
    }
    
    public function getUnitsByType(string $type)
    {
        return Unit::aktif()
            ->where('jenis_unit', $type)
            ->orderBy('nama_unit')
            ->get();
    }
    
    public function getNearbyUnits(?float $latitude, ?float $longitude, float $radius)
    {
        if (!$latitude || !$longitude) {
            return collect();
        }
        
        $units = Unit::aktif()->get();
        
        return $units->filter(function ($unit) use ($latitude, $longitude, $radius) {
            $unitCoords = $this->extractCoordinates($unit->lokasi);
            
            if (!$unitCoords) {
                return false;
            }
            
            $distance = $this->calculateDistance(
                $latitude, $longitude,
                $unitCoords['lat'], $unitCoords['lng']
            );
            
            return $distance <= $radius;
        })->values();
    }
    
    public function getPopularUnits()
    {
        return Unit::aktif()
            ->withCount(['visits' => function ($query) {
                $query->whereDate('tanggal', '>=', Carbon::now()->subDays(30));
            }])
            ->orderBy('visits_count', 'desc')
            ->limit(10)
            ->get();
    }
    
    public function trackUnitVisit(string $unitId, string $sessionId): UnitVisit
    {
        $this->ensureVisitorSession($sessionId);
        
        return UnitVisit::create([
            'unit_id' => $unitId,
            'session_id' => $sessionId,
            'tanggal' => Carbon::today(),
            'waktu_masuk' => Carbon::now(),
            'metadata' => ['tracked_via' => 'visitor_api']
        ]);
    }
    
    public function endUnitVisit(string $visitId): UnitVisit
    {
        $visit = UnitVisit::findOrFail($visitId);
        
        $visit->update([
            'waktu_keluar' => Carbon::now(),
            'durasi_detik' => Carbon::now()->diffInSeconds($visit->waktu_masuk)
        ]);
        
        return $visit;
    }
    
    private function ensureVisitorSession(string $sessionId): void
    {
        VisitorSession::firstOrCreate(
            ['session_id' => $sessionId],
            [
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'terakhir_aktivitas' => Carbon::now()
            ]
        );
    }
    
    private function extractCoordinates(string $location): ?array
    {
        preg_match('/(\-?\d+\.\d+).*?(\-?\d+\.\d+)/', $location, $matches);
        
        if (count($matches) >= 3) {
            return [
                'lat' => floatval($matches[1]),
                'lng' => floatval($matches[2])
            ];
        }
        
        return null;
    }
    
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371;
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c;
    }
}