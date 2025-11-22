<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Handle user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid credentials',
                'errors' => [
                    'email' => ['Email atau password salah']
                ]
            ], 401);
        }

        $user = $request->user();
        
        // Create token for the user
        $token = $user->createToken('auth_token')->plainTextToken;

        // Get user permissions
        $permissions = $user->getAllPermissions()->pluck('name')->toArray();
        
        // Get user roles
        $roles = $user->getRoleNames()->toArray();

        return response()->json([
            'message' => 'Login successful',
            'data' => [
                'token' => $token,
                'user' => new UserResource($user),
                'permissions' => $permissions,
                'roles' => $roles,
            ]
        ]);
    }

    /**
     * Handle user logout request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Revoke the token that was used to authenticate the current request
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successful',
            'data' => null
        ]);
    }

    /**
     * Get the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(Request $request)
    {
        $user = $request->user();
        
        // Get user permissions
        $permissions = $user->getAllPermissions()->pluck('name')->toArray();
        
        // Get user roles
        $roles = $user->getRoleNames()->toArray();

        return response()->json([
            'message' => 'User data retrieved successfully',
            'data' => [
                'user' => new UserResource($user),
                'permissions' => $permissions,
                'roles' => $roles,
            ]
        ]);
    }
}
