<?php

namespace App\Services\User;

use App\Models\User;
use App\Services\Logging\AdminActionLogger;
use Illuminate\Support\Facades\Auth;

class UserService
{
    protected $logger;
    
    public function __construct(AdminActionLogger $logger = null)
    {
        $this->logger = $logger;
    }
    
    public function createUser(array $data): User
    {
        $user = User::create($data);
        
        if ($this->logger) {
            $this->logger->log('created user', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
            ]);
        }
        
        return $user;
    }
    
    public function updateUser(User $user, array $data): User
    {
        // Prevent self-demotion
        if ($user->id === Auth::id() && isset($data['role']) && $data['role'] !== 'admin') {
            throw new \Exception('You cannot change your own role from admin');
        }
        
        $oldData = $user->toArray();
        $user->update($data);
        
        if ($this->logger) {
            $this->logger->log('updated user', [
                'user_id' => $user->id,
                'email' => $user->email,
                'changes' => array_keys($data),
                'old_data' => $oldData,
            ]);
        }
        
        return $user->fresh();
    }
    
    public function deleteUser(User $user): void
    {
        // Prevent self-deletion
        if ($user->id === Auth::id()) {
            throw new \Exception('You cannot delete your own account');
        }
        
        $userData = $user->toArray();
        $user->delete();
        
        if ($this->logger) {
            $this->logger->log('deleted user', [
                'user_id' => $userData['id'],
                'email' => $userData['email'],
                'role' => $userData['role'],
            ]);
        }
    }
    
    public function getUserWithActivities(User $user): array
    {
        $user->loadCount(['ratings', 'messages']);
        
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
    
    public function validateUserData(array $data, User $user = null): array
    {
        $errors = [];
        
        if (isset($data['email'])) {
            $query = User::where('email', $data['email']);
            if ($user) {
                $query->where('id', '!=', $user->id);
            }
            
            if ($query->exists()) {
                $errors[] = 'Email already exists';
            }
        }
        
        if (isset($data['role']) && !in_array($data['role'], ['admin', 'reviewer', 'user'])) {
            $errors[] = 'Invalid role specified';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}