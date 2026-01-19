<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\Hash;

class UserValidationService
{
    public function validateCreate(array $data): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:admin,reviewer,user',
            'is_active' => 'boolean',
        ];
        
        return $this->validate($data, $rules);
    }
    
    public function validateUpdate(array $data, User $user): array
    {
        $rules = [
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', \Illuminate\Validation\Rule::unique('users')->ignore($user->id)],
            'password' => 'sometimes|string|min:8|confirmed',
            'role' => 'sometimes|string|in:admin,reviewer,user',
            'is_active' => 'sometimes|boolean',
        ];
        
        return $this->validate($data, $rules);
    }
    
    public function validateBulkAction(array $data): array
    {
        $rules = [
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'action' => 'required|in:activate,deactivate,delete,change_role',
            'role' => 'required_if:action,change_role|in:admin,reviewer,user',
        ];
        
        return $this->validate($data, $rules);
    }
    
    private function validate(array $data, array $rules): array
    {
        $validator = \Validator::make($data, $rules);
        
        if ($validator->fails()) {
            return [
                'valid' => false,
                'errors' => $validator->errors()->toArray(),
            ];
        }
        
        // Additional custom validations
        $customErrors = $this->customValidations($data, $rules);
        
        return [
            'valid' => empty($customErrors),
            'errors' => $customErrors,
        ];
    }
    
    private function customValidations(array $data, array $rules): array
    {
        $errors = [];
        
        // Password strength validation
        if (isset($data['password'])) {
            if (!$this->isStrongPassword($data['password'])) {
                $errors['password'] = ['Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character'];
            }
        }
        
        // Email domain validation (optional)
        if (isset($data['email']) && isset($rules['email'])) {
            if (!$this->isValidEmailDomain($data['email'])) {
                $errors['email'] = ['Email domain is not allowed'];
            }
        }
        
        // Role transition validation
        if (isset($data['role'])) {
            // Add any role transition logic here
        }
        
        return $errors;
    }
    
    private function isStrongPassword(string $password): bool
    {
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password);
    }
    
    private function isValidEmailDomain(string $email): bool
    {
        // Example: Allow only specific domains
        $allowedDomains = ['gmail.com', 'yahoo.com', 'outlook.com', 'company.com'];
        $domain = substr(strrchr($email, "@"), 1);
        
        // Return true for all domains (customize as needed)
        return true;
        // return in_array($domain, $allowedDomains);
    }
    
    public function prepareUserData(array $data): array
    {
        // Hash password if present
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        
        // Set default values
        if (!isset($data['is_active'])) {
            $data['is_active'] = true;
        }
        
        if (!isset($data['role'])) {
            $data['role'] = 'user';
        }
        
        return $data;
    }
    
    public function validateRoleChange(User $changer, User $target, string $newRole): array
    {
        $errors = [];
        
        // Can't change your own role from admin
        if ($target->id === $changer->id && $target->role === 'admin' && $newRole !== 'admin') {
            $errors[] = 'You cannot change your own role from admin';
        }
        
        // Only admins can change roles
        if ($changer->role !== 'admin') {
            $errors[] = 'Only administrators can change user roles';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}