<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;

class LoginController extends Controller
{
    // app/Http/Controllers/Auth/LoginController.php
    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);

            $user = User::where('email', $credentials['email'])->first();

            if (!$user || !\Hash::check($credentials['password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid email or password'
                ], 401);
            }

            if (!in_array($user->role, ['admin', 'super_admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Admin only.'
                ], 403);
            }

            // **DEBUG: Cek sebelum login**
            \Log::info('Before Auth::login', [
                'session_id' => session()->getId(),
                'user_id' => $user->id
            ]);

            // **LOGIN dengan cara yang benar**
            \Auth::login($user, true); // true untuk "remember me"

            // **MANUAL set session jika perlu**
            session()->put('auth.user_id', $user->id);
            session()->put('auth.user_email', $user->email);
            session()->save(); // Force save

            // **DEBUG: Cek setelah login**
            \Log::info('After Auth::login', [
                'session_id' => session()->getId(),
                'auth_check' => \Auth::check() ? 'YES' : 'NO',
                'auth_id' => \Auth::id(),
                'session_user_id' => session('auth.user_id')
            ]);

            // Buat Sanctum token untuk API calls
            $token = $user->createToken('web-auth-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'nama' => $user->nama,
                        'email' => $user->email,
                        'role' => $user->role
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Login error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function check(Request $request)
    {
        try {
            $user = $request->user();

            // Jika user tidak ditemukan via request->user(), coba cari via token
            if (!$user && $request->bearerToken()) {
                $token = PersonalAccessToken::findToken($request->bearerToken());

                if ($token) {
                    $user = $token->tokenable;
                    Log::info('User found via token lookup', [
                        'user_id' => $user->id ?? 'null',
                        'token_id' => $token->id
                    ]);
                }
            }

            if (!$user) {
                Log::info('Auth check failed - No authenticated user');
                return response()->json([
                    'success' => false,
                    'authenticated' => false,
                    'message' => 'Not authenticated'
                ], 401);
            }

            Log::info('Auth check successful', [
                'user_id' => $user->id,
                'user_name' => $user->nama ?? 'N/A',
                'user_role' => $user->role
            ]);

            return response()->json([
                'success' => true,
                'authenticated' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'nama' => $user->nama,
                        'email' => $user->email,
                        'role' => $user->role,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Auth check error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Authentication check failed'
            ], 500);
        }
    }
}