<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserRoleService
{
    public function changeRole(User $user, string $newRole): User
    {
        // Prevent self-demotion from admin
        if ($user->id === Auth::id() && $user->role === 'admin' && $newRole !== 'admin') {
            throw new \Exception('You cannot change your own role from admin');
        }
        
        if (!in_array($newRole, ['admin', 'reviewer', 'user'])) {
            throw new \Exception('Invalid role specified');
        }
        
        $user->update(['role' => $newRole]);
        
        return $user;
    }
    
    public function getRoleHierarchy(): array
    {
        return [
            'admin' => [
                'level' => 3,
                'permissions' => ['all'],
                'description' => 'Full system access',
            ],
            'reviewer' => [
                'level' => 2,
                'permissions' => ['view_units', 'submit_ratings', 'view_reports'],
                'description' => 'Can submit ratings and view reports',
            ],
            'user' => [
                'level' => 1,
                'permissions' => ['view_units', 'submit_ratings'],
                'description' => 'Basic user access',
            ],
        ];
    }
    
    public function canChangeRole(User $changer, User $target, string $newRole): bool
    {
        // Can't change your own role from admin
        if ($target->id === $changer->id && $target->role === 'admin' && $newRole !== 'admin') {
            return false;
        }
        
        // Only admins can change roles
        if ($changer->role !== 'admin') {
            return false;
        }
        
        // Can't promote someone above your own level
        $hierarchy = $this->getRoleHierarchy();
        $changerLevel = $hierarchy[$changer->role]['level'] ?? 0;
        $newRoleLevel = $hierarchy[$newRole]['level'] ?? 0;
        
        return $newRoleLevel <= $changerLevel;
    }
    
    public function getUserCountByRole(): array
    {
        return [
            'admin' => User::where('role', 'admin')->count(),
            'reviewer' => User::where('role', 'reviewer')->count(),
            'user' => User::where('role', 'user')->count(),
            'total' => User::count(),
        ];
    }
    
    public function getUsersByRole(string $role)
    {
        return User::where('role', $role)
            ->select('id', 'name', 'email', 'created_at')
            ->orderBy('name')
            ->get();
    }
}