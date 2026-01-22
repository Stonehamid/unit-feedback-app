<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display listing of users
     */
    public function index(Request $request)
    {
        $query = User::query();
        
        // Filter by role
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }
        
        // Search by name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $users = $query->latest()->paginate(15);
        
        return [
            'users' => $users,
            'stats' => [
                'total' => User::count(),
                'admins' => User::where('role', 'admin')->count(),
                'reviewers' => User::where('role', 'reviewer')->count(),
                'users' => User::where('role', 'user')->count(),
            ]
        ];
    }

    /**
     * Display single user with details
     */
    public function show(User $user)
    {
        $user->loadCount(['ratings', 'messages']);
        
        // Get user's recent activities
        $recentRatings = $user->ratings()
                             ->with('unit:id,name')
                             ->latest()
                             ->limit(5)
                             ->get();
        
        $recentMessages = $user->messages()
                              ->with('unit:id,name')
                              ->latest()
                              ->limit(5)
                              ->get();
        
        return [
            'user' => $user,
            'recent_activities' => [
                'ratings' => $recentRatings,
                'messages' => $recentMessages,
            ],
            'total_counts' => [
                'ratings' => $user->ratings_count,
                'messages' => $user->messages_count,
            ]
        ];
    }

    /**
     * Update user role or details
     */
    public function update(Request $request, User $user)
    {
        // Prevent admin from demoting themselves
        if ($user->id === auth()->id() && $request->has('role') && $request->role !== 'admin') {
            return response()->json([
                'message' => 'You cannot change your own role from admin'
            ], 403);
        }
        
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('users')->ignore($user->id)],
            'role' => 'sometimes|string|in:admin,reviewer,user',
            'is_active' => 'sometimes|boolean',
        ]);
        
        $user->update($validated);
        
        return [
            'user' => $user,
            'message' => 'User updated successfully'
        ];
    }

    /**
     * Delete user
     */
    public function destroy(User $user)
    {
        // Prevent admin from deleting themselves
        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => 'You cannot delete your own account'
            ], 403);
        }
        
        // Optional: Soft delete or permanent delete
        $user->delete();
        
        return [
            'message' => 'User deleted successfully'
        ];
    }

    /**
     * Get user statistics for dashboard
     */
    public function statistics()
    {
        $totalUsers = User::count();
        $newUsersThisMonth = User::whereMonth('created_at', now()->month)->count();
        $activeUsers = User::where('is_active', true)->count();
        
        // User growth per month (last 6 months)
        $userGrowth = User::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
                         ->where('created_at', '>=', now()->subMonths(6))
                         ->groupBy('month')
                         ->orderBy('month')
                         ->get();
        
        return [
            'total_users' => $totalUsers,
            'new_users_this_month' => $newUsersThisMonth,
            'active_users' => $activeUsers,
            'role_distribution' => [
                'admin' => User::where('role', 'admin')->count(),
                'reviewer' => User::where('role', 'reviewer')->count(),
                'user' => User::where('role', 'user')->count(),
            ],
            'growth_data' => $userGrowth,
        ];
    }

    /**
     * Bulk actions (activate/deactivate users)
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'action' => 'required|in:activate,deactivate,delete,change_role',
            'role' => 'required_if:action,change_role|in:admin,reviewer,user',
        ]);
        
        $users = User::whereIn('id', $request->user_ids)
                    ->where('id', '!=', auth()->id()) // Exclude current admin
                    ->get();
        
        $count = 0;
        
        foreach ($users as $user) {
            switch ($request->action) {
                case 'activate':
                    $user->update(['is_active' => true]);
                    $count++;
                    break;
                    
                case 'deactivate':
                    $user->update(['is_active' => false]);
                    $count++;
                    break;
                    
                case 'change_role':
                    $user->update(['role' => $request->role]);
                    $count++;
                    break;
                    
                case 'delete':
                    $user->delete();
                    $count++;
                    break;
            }
        }
        
        return [
            'message' => "Action completed successfully. {$count} users affected."
        ];
    }
}