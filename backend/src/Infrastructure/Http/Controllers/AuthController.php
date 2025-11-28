<?php

namespace NebulaDesk\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use NebulaDesk\Application\DTOs\LoginUserDTO;
use NebulaDesk\Application\DTOs\RegisterUserDTO;
use NebulaDesk\Application\UseCases\LoginUserUseCase;
use NebulaDesk\Application\UseCases\RegisterUserUseCase;

class AuthController extends Controller
{
    public function __construct(
        private RegisterUserUseCase $registerUserUseCase,
        private LoginUserUseCase $loginUserUseCase
    ) {
    }

    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        // Check if this is the first user (bootstrap scenario)
        $isFirstUser = \App\Models\User::count() === 0;

        if ($isFirstUser) {
            // First user flow: create admin with organization
            $organizationName = $request->input('organization_name', $request->name . "'s Organization");
            
            // Create organization for the first user
            $organization = \App\Models\Organization::create([
                'name' => $organizationName,
                'is_active' => true
            ]);

            // Create DTO with admin role and organization
            $dto = new RegisterUserDTO(
                name: $request->name,
                email: $request->email,
                password: $request->password,
                organizationName: $organizationName,
                organizationId: $organization->id,
                role: 'admin'
            );

            $user = $this->registerUserUseCase->execute($dto);

            // Get Eloquent model for token creation and audit log
            $eloquentUser = \App\Models\User::find($user->id);

            // Log bootstrap event in audit logs
            try {
                \App\Models\AuditLog::create([
                    'user_id' => $eloquentUser->id,
                    'action' => 'system_bootstrap_admin_created',
                    'entity_type' => 'User',
                    'entity_id' => $eloquentUser->id,
                    'created_at' => now()
                ]);
            } catch (\Exception $e) {
                // Log audit error but don't fail registration
                Log::warning('Failed to create audit log for bootstrap admin', ['error' => $e->getMessage()]);
            }
        } else {
            // Regular user registration: create customer with default organization
            $dto = new RegisterUserDTO(
                name: $request->name,
                email: $request->email,
                password: $request->password,
                organizationName: null,
                organizationId: 1, // Default organization
                role: 'customer'
            );

            $user = $this->registerUserUseCase->execute($dto);
            $eloquentUser = \App\Models\User::find($user->id);
        }

        // Create token for immediate login after registration
        $token = $eloquentUser->createToken('auth_token')->plainTextToken;

        // Send verification email
        $eloquentUser->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'User registered successfully',
            'token' => $token,
            'user' => [
                'id' => $eloquentUser->id,
                'name' => $eloquentUser->name,
                'email' => $eloquentUser->email,
                'organization_id' => $eloquentUser->organization_id,
                'role' => $eloquentUser->role,
            ]
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $dto = new LoginUserDTO(
            email: $request->email,
            password: $request->password
        );

        $result = $this->loginUserUseCase->execute($dto);

        // Handle locked account
        if ($result['status'] === 'locked') {
            return response()->json([
                'message' => 'Your account has been locked due to multiple failed login attempts. Please contact an administrator.',
                'error' => 'account_locked'
            ], 423); // 423 Locked
        }

        // Handle invalid credentials
        if ($result['status'] === 'invalid') {
            return response()->json([
                'message' => 'Email or password is incorrect.',
                'error' => 'invalid_credentials'
            ], 401);
        }

        // Success - generate token
        $user = $result['user'];
        
        // Get Eloquent model to create token
        $eloquentUser = \App\Models\User::find($user->id);

        $token = $eloquentUser->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'organization_id' => $user->organizationId,
                'role' => $user->role,
                'email_verified' => $eloquentUser->hasVerifiedEmail(),
            ]
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete(); // Invalidate only the current token
        }

        return response()->json([
            'message' => 'Sesión cerrada correctamente',
        ]);
    }
}
