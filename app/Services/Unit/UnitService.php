<?php

namespace App\Services\Unit;

use App\Models\Unit;
use App\Services\Logging\AdminActionLogger;
use Illuminate\Support\Facades\Storage;

class UnitService
{
    protected $logger;
    
    public function __construct(AdminActionLogger $logger)
    {
        $this->logger = $logger;
    }
    
    public function createUnit(array $data, $photo = null): Unit
    {
        if ($photo) {
            $data['photo'] = $photo->store('unit-photos', 'public');
        }
        
        $unit = Unit::create($data);
        
        $this->logger->log('created unit', [
            'unit_id' => $unit->id,
            'unit_name' => $unit->name,
        ]);
        
        return $unit;
    }
    
    public function updateUnit(Unit $unit, array $data, $photo = null, bool $removePhoto = false): Unit
    {
        $oldData = $unit->toArray();
        
        // Handle photo upload/delete
        if ($photo) {
            // Delete old photo if exists
            if ($unit->photo) {
                Storage::disk('public')->delete($unit->photo);
            }
            $data['photo'] = $photo->store('unit-photos', 'public');
        } elseif ($removePhoto && $unit->photo) {
            Storage::disk('public')->delete($unit->photo);
            $data['photo'] = null;
        }
        
        $unit->update($data);
        
        $this->logger->log('updated unit', [
            'unit_id' => $unit->id,
            'unit_name' => $unit->name,
            'changes' => array_keys($data),
        ]);
        
        return $unit->fresh();
    }
    
    public function deleteUnit(Unit $unit, bool $force = false): array
    {
        $hasRelations = $this->hasRelations($unit);
        
        if ($hasRelations && !$force) {
            return [
                'success' => false,
                'message' => 'Unit cannot be deleted because it has related data.',
                'has_ratings' => $unit->ratings()->exists(),
                'has_messages' => $unit->messages()->exists(),
                'has_reports' => $unit->reports()->exists(),
            ];
        }
        
        // Force delete with relations
        if ($force && $hasRelations) {
            $unit->ratings()->delete();
            $unit->messages()->delete();
            $unit->reports()->delete();
            
            $this->logger->log('force deleted unit with relations', [
                'unit_id' => $unit->id,
                'unit_name' => $unit->name,
            ]);
        }
        
        // Delete photo
        if ($unit->photo) {
            Storage::disk('public')->delete($unit->photo);
        }
        
        $unitData = $unit->toArray();
        $unit->delete();
        
        $this->logger->log('deleted unit', [
            'unit_id' => $unitData['id'],
            'unit_name' => $unitData['name'],
            'force' => $force,
        ]);
        
        return [
            'success' => true,
            'message' => 'Unit deleted successfully',
        ];
    }
    
    public function getUnitWithDetails(Unit $unit): array
    {
        $unit->load([
            'ratings' => function ($query) {
                $query->latest()->limit(20);
            },
            'messages' => function ($query) {
                $query->latest()->limit(20);
            },
            'reports' => function ($query) {
                $query->with('admin:id,name')->latest()->limit(10);
            }
        ]);
        
        $unit->loadCount(['ratings', 'messages', 'reports']);
        $unit->loadAvg('ratings', 'rating');
        
        $ratingDistribution = $this->getRatingDistribution($unit->id);
        
        return [
            'unit' => $unit,
            'statistics' => [
                'rating_distribution' => $ratingDistribution,
                'top_reviewers' => $this->getTopReviewers($unit->id),
                'average_by_month' => $this->getMonthlyAverage($unit->id),
            ],
        ];
    }
    
    private function hasRelations(Unit $unit): bool
    {
        return $unit->ratings()->exists() || 
               $unit->messages()->exists() || 
               $unit->reports()->exists();
    }
    
    private function getRatingDistribution(int $unitId)
    {
        return \DB::table('ratings')
            ->where('unit_id', $unitId)
            ->select('rating', \DB::raw('count(*) as count'))
            ->groupBy('rating')
            ->orderBy('rating')
            ->get()
            ->pluck('count', 'rating');
    }
    
    private function getTopReviewers(int $unitId)
    {
        return \DB::table('ratings')
            ->where('unit_id', $unitId)
            ->select('reviewer_name', \DB::raw('count(*) as count, AVG(rating) as avg_rating'))
            ->groupBy('reviewer_name')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();
    }
    
    private function getMonthlyAverage(int $unitId)
    {
        return \DB::table('ratings')
            ->where('unit_id', $unitId)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, AVG(rating) as avg_rating')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(6)
            ->get();
    }
}