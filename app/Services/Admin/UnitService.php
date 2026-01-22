<?php

namespace App\Services\Admin;

use App\Models\Employee;
use App\Models\RatingCategory;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class UnitService
{
    public function getUnits(array $filters = [])
    {
        $query = Unit::withCount(['ratings', 'visits', 'employees', 'reports']);
        
        if (isset($filters['jenis'])) {
            $query->where('jenis_unit', $filters['jenis']);
        }
        
        if (isset($filters['status'])) {
            $query->where('status_aktif', $filters['status'] === 'aktif');
        }
        
        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('nama_unit', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('kode_unit', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('lokasi', 'like', '%' . $filters['search'] . '%');
            });
        }
        
        if (isset($filters['gedung'])) {
            $query->where('gedung', $filters['gedung']);
        }
        
        $sort = $filters['sort'] ?? 'nama_unit';
        $order = $filters['order'] ?? 'asc';
        
        return $query->orderBy($sort, $order)->paginate($filters['per_page'] ?? 20);
    }
    
    public function createUnit(array $data): Unit
    {
        return Unit::create($data);
    }
    
    public function getUnitDetail(string $id)
    {
        $unit = Unit::withCount(['ratings', 'visits', 'employees', 'reports'])
            ->with(['employees' => function ($query) {
                $query->orderBy('status')->orderBy('nama');
            }])
            ->findOrFail($id);
        
        $averageRating = $unit->ratings()
            ->has('scores')
            ->with('scores')
            ->get()
            ->avg('rata_rata');
        
        $ratingByCategory = RatingCategory::where('unit_id', $id)
            ->with(['scores' => function ($query) {
                $query->select('rating_category_id', DB::raw('AVG(skor) as average_score'))
                    ->groupBy('rating_category_id');
            }])
            ->get()
            ->map(function ($category) {
                return [
                    'kategori' => $category->nama_kategori,
                    'rata_rata' => $category->scores->first()->average_score ?? 0
                ];
            });
        
        return [
            'unit' => $unit,
            'stats' => [
                'average_rating' => round($averageRating ?? 0, 1),
                'total_ratings' => $unit->ratings_count,
                'total_visits' => $unit->visits_count,
                'total_employees' => $unit->employees_count,
                'total_reports' => $unit->reports_count
            ],
            'rating_by_category' => $ratingByCategory,
            'recent_visits' => $unit->visits()
                ->orderBy('waktu_masuk', 'desc')
                ->limit(10)
                ->get(),
            'recent_ratings' => $unit->ratings()
                ->with('scores.category')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
        ];
    }
    
    public function updateUnit(string $id, array $data): Unit
    {
        $unit = Unit::findOrFail($id);
        $unit->update($data);
        
        return $unit;
    }
    
    public function deleteUnit(string $id): void
    {
        $unit = Unit::findOrFail($id);
        $unit->delete();
    }
    
    public function toggleUnitStatus(string $id): Unit
    {
        $unit = Unit::findOrFail($id);
        $unit->update([
            'status_aktif' => !$unit->status_aktif
        ]);
        
        return $unit;
    }
    
    public function getUnitCategories(string $unitId)
    {
        return RatingCategory::where('unit_id', $unitId)
            ->orderBy('urutan')
            ->get();
    }
    
    public function getUnitEmployees(string $unitId, array $filters = [])
    {
        $query = Employee::where('unit_id', $unitId);
        
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('nama', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('jabatan', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('bidang', 'like', '%' . $filters['search'] . '%');
            });
        }
        
        return $query->orderBy('nama')->paginate($filters['per_page'] ?? 20);
    }
    
    public function createEmployee(string $unitId, array $data): Employee
    {
        $data['unit_id'] = $unitId;
        return Employee::create($data);
    }
    
    public function getEmployeeDetail(string $unitId, string $id): Employee
    {
        return Employee::where('unit_id', $unitId)->findOrFail($id);
    }
    
    public function updateEmployee(string $unitId, string $id, array $data): Employee
    {
        $employee = Employee::where('unit_id', $unitId)->findOrFail($id);
        $employee->update($data);
        
        return $employee;
    }
    
    public function deleteEmployee(string $unitId, string $id): void
    {
        $employee = Employee::where('unit_id', $unitId)->findOrFail($id);
        $employee->delete();
    }
    
    public function updateEmployeeStatus(string $unitId, string $id, string $status): Employee
    {
        $employee = Employee::where('unit_id', $unitId)->findOrFail($id);
        $employee->update(['status' => $status]);
        
        return $employee;
    }
}