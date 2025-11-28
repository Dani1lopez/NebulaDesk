<?php

namespace NebulaDesk\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use NebulaDesk\Application\DTOs\UpdateUserProfileDTO;
use NebulaDesk\Application\UseCases\UpdateUserProfileUseCase;
use NebulaDesk\Application\UseCases\GetUserUseCase;
use NebulaDesk\Application\UseCases\UpdateUserUseCase;
use NebulaDesk\Application\UseCases\DeleteUserUseCase;
use NebulaDesk\Application\UseCases\ListUsersUseCase;
use NebulaDesk\Application\UseCases\LockUserUseCase;
use NebulaDesk\Application\UseCases\UnlockUserUseCase;
use NebulaDesk\Application\DTOs\UpdateUserDTO;
use NebulaDesk\Application\DTOs\LockUserDTO;
use NebulaDesk\Application\DTOs\UnlockUserDTO;
use App\Models\User;

class UserController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private UpdateUserProfileUseCase $updateUserProfileUseCase,
        private GetUserUseCase $getUserUseCase,
        private UpdateUserUseCase $updateUserUseCase,
        private DeleteUserUseCase $deleteUserUseCase,
        private ListUsersUseCase $listUsersUseCase,
        private LockUserUseCase $lockUserUseCase,
        private UnlockUserUseCase $unlockUserUseCase
    ) {
    }

    /**
     * List all users (admin only)
     */
    public function index(Request $request): JsonResponse
    {
        // Authorization: only admin can list all users
        $this->authorize('viewAny', User::class);

        $users = $this->listUsersUseCase->execute();

        return response()->json([
            'users' => array_map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'organization_id' => $user->organizationId,
                    'is_locked' => $user->isLocked,
                    'locked_at' => $user->lockedAt?->format('Y-m-d H:i:s'),
                    'locked_by' => $user->lockedBy,
                ];
            }, $users)
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $request->user()->id,
            'avatar' => 'sometimes|image|max:2048', // Max 2MB
        ]);

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $avatarPath = '/storage/' . $path;
        }

        $dto = new UpdateUserProfileDTO(
            userId: $request->user()->getAuthIdentifier(),
            name: $request->name,
            email: $request->email,
            avatarPath: $avatarPath
        );

        $user = $this->updateUserProfileUseCase->execute($dto);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
            ]
        ]);
    }

    /**
     * Show a specific user
     */
    public function show(int $id): JsonResponse
    {
        $user = $this->getUserUseCase->execute($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Authorization: can view own profile or admin can view any
        $eloquentUser = User::find($id);
        $this->authorize('view', $eloquentUser);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'organization_id' => $user->organizationId,
                'is_locked' => $user->isLocked,
                'locked_at' => $user->lockedAt?->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    /**
     * Update user details
     */
    public function update(Request $request, int $id): JsonResponse
    {
        // Authorization check
        $eloquentUser = User::find($id);
        if (!$eloquentUser) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $this->authorize('update', $eloquentUser);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'role' => 'sometimes|in:admin,agent,customer,owner',
            'password' => 'sometimes|string|min:8',
        ]);

        try {
            $dto = new UpdateUserDTO(
                userId: $id,
                name: $request->input('name'),
                email: $request->input('email'),
                role: $request->input('role'),
                password: $request->input('password')
            );

            $user = $this->updateUserUseCase->execute($dto);

            return response()->json([
                'message' => 'User updated successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Delete a user
     */
    public function destroy(int $id): JsonResponse
    {
        // Authorization check
        $eloquentUser = User::find($id);
        if (!$eloquentUser) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $this->authorize('delete', $eloquentUser);

        try {
            $this->deleteUserUseCase->execute($id);
            return response()->json(['message' => 'User deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Lock a user account (admin only)
     */
    public function lock(Request $request, int $id): JsonResponse
    {
        // Authorization check
        $eloquentUser = User::find($id);
        if (!$eloquentUser) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $this->authorize('lock', $eloquentUser);

        try {
            $dto = new LockUserDTO(
                userId: $id,
                adminId: $request->user()->id
            );

            $this->lockUserUseCase->execute($dto);

            return response()->json([
                'message' => 'User locked successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Unlock a user account (admin only)
     */
    public function unlock(Request $request, int $id): JsonResponse
    {
        // Authorization check
        $eloquentUser = User::find($id);
        if (!$eloquentUser) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $this->authorize('unlock', $eloquentUser);

        try {
            $dto = new UnlockUserDTO(
                userId: $id,
                adminId: $request->user()->id
            );

            $this->unlockUserUseCase->execute($dto);

            return response()->json([
                'message' => 'User unlocked successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
