<?php

namespace App\Services\User;

use App\Models\User;
use App\Services\Logging\AdminActionLogger;

class UserBulkActionService
{
    protected $logger;
    protected $userService;
    
    public function __construct(
        AdminActionLogger $logger,
        UserService $userService = null
    ) {
        $this->logger = $logger;
        $this->userService = $userService ?? new UserService($logger);
    }
    
    public function handleBulkAction(array $userIds, string $action, array $data = []): array
    {
        $users = User::whereIn('id', $userIds)->get();
        $count = 0;
        $failed = [];
        
        foreach ($users as $user) {
            try {
                switch ($action) {
                    case 'activate':
                        $user->update(['is_active' => true]);
                        $count++;
                        break;
                        
                    case 'deactivate':
                        $user->update(['is_active' => false]);
                        $count++;
                        break;
                        
                    case 'change_role':
                        if (isset($data['role'])) {
                            $user->update(['role' => $data['role']]);
                            $count++;
                        }
                        break;
                        
                    case 'delete':
                        $this->userService->deleteUser($user);
                        $count++;
                        break;
                        
                    case 'update':
                        if (!empty($data)) {
                            $this->userService->updateUser($user, $data);
                            $count++;
                        }
                        break;
                }
            } catch (\Exception $e) {
                $failed[] = [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ];
            }
        }
        
        $this->logger->logBulkAction($action, $userIds, [
            'success_count' => $count,
            'failed_count' => count($failed),
            'data' => $data,
        ]);
        
        // FIX: Convert array count to string properly
        $failedCount = count($failed);
        
        return [
            'success_count' => $count,
            'failed_count' => $failedCount,
            'failed_users' => $failed,
            'message' => "Bulk action completed. {$count} users affected" . 
                        ($failedCount > 0 ? ", {$failedCount} failed" : ""),
        ];
    }
    
    public function validateBulkAction(array $userIds, string $action, array $data = []): array
    {
        $errors = [];
        $warnings = [];
        
        // Check if all users exist
        $existingCount = User::whereIn('id', $userIds)->count();
        if ($existingCount !== count($userIds)) {
            $errors[] = 'Some user IDs are invalid';
        }
        
        // Check action-specific validations
        switch ($action) {
            case 'change_role':
                if (!isset($data['role']) || !in_array($data['role'], ['admin', 'reviewer', 'user'])) {
                    $errors[] = 'Invalid or missing role for change_role action';
                }
                break;
                
            case 'delete':
                // Warn about data loss
                $users = User::whereIn('id', $userIds)->get();
                $usersWithActivities = $users->filter(function($user) {
                    return $user->ratings()->exists() || $user->messages()->exists();
                });
                
                if ($usersWithActivities->count() > 0) {
                    $warnings[] = "{$usersWithActivities->count()} users have activities that will be deleted";
                }
                break;
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }
    
    public function getBulkActionSummary(array $userIds): array
    {
        $users = User::whereIn('id', $userIds)->get();
        
        return [
            'total_selected' => $users->count(),
            'role_distribution' => $users->groupBy('role')->map->count(),
            'status_distribution' => $users->groupBy('is_active')->map->count(),
            'with_activities' => $users->filter(function($user) {
                return $user->ratings()->exists() || $user->messages()->exists();
            })->count(),
            'created_date_range' => [
                'oldest' => $users->min('created_at'),
                'newest' => $users->max('created_at'),
            ],
        ];
    }
}