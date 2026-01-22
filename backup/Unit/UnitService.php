<?php

namespace App\Services\Unit;

use App\Models\Unit;
use Illuminate\Support\Facades\Storage;

class UnitService
{
    public function createUnit(array $data, $photo = null): Unit
    {
        if ($photo) {
            $data['photo'] = $photo->store('unit-photos', 'public');
        }

        $data['status_changed_at'] = now();
        
        return Unit::create($data);
    }
    
    public function updateUnit(Unit $unit, array $data, $photo = null, bool $removePhoto = false): Unit
    {
        if ($photo) {
            if ($unit->photo) {
                Storage::disk('public')->delete($unit->photo);
            }
            $data['photo'] = $photo->store('unit-photos', 'public');
        } elseif ($removePhoto && $unit->photo) {
            Storage::disk('public')->delete($unit->photo);
            $data['photo'] = null;
        }

        if (isset($data['status']) && $data['status'] !== $unit->status) {
            $data['status_changed_at'] = now();
        }

        $unit->update($data);
        
        return $unit->fresh();
    }
    
    public function deleteUnit(Unit $unit): bool
    {
        if ($unit->photo) {
            Storage::disk('public')->delete($unit->photo);
        }
        
        return $unit->delete();
    }
    
    public function updateStatus(Unit $unit, string $status): bool
    {
        return $unit->update([
            'status' => $status,
            'status_changed_at' => now()
        ]);
    }
}