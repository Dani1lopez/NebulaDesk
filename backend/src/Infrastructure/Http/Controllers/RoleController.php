<?php

namespace NebulaDesk\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use NebulaDesk\Application\DTOs\CreateRoleDTO;
use NebulaDesk\Application\DTOs\AssignRoleDTO;
use NebulaDesk\Application\UseCases\CreateRoleUseCase;
use NebulaDesk\Application\UseCases\ListRolesUseCase;
use NebulaDesk\Application\UseCases\AssignRoleUseCase;

class RoleController extends Controller
{
    public function __construct(
        private CreateRoleUseCase $createRoleUseCase,
        private ListRolesUseCase $listRolesUseCase,
        private AssignRoleUseCase $assignRoleUseCase
    ) {
    }

    /**
     * List all roles
     */
    public function index(): JsonResponse
    {
        $roles = $this->listRolesUseCase->execute();
        $data = array_map(fn($role) => [
            'id' => $role->id,
            'name' => $role->name,
            'description' => $role->description,
        ], $roles);

        return response()->json(['roles' => $data]);
    }

    /**
     * Create a new role
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        try {
            $dto = new CreateRoleDTO(
                name: $request->input('name'),
                description: $request->input('description')
            );

            $role = $this->createRoleUseCase->execute($dto);

            return response()->json([
                'message' => 'Role created successfully',
                'role' => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'description' => $role->description,
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Assign a role to a user
     */
    public function assignToUser(Request $request, int $userId): JsonResponse
    {
        $request->validate([
            'role_id' => 'required|integer|exists:roles,id',
        ]);

        try {
            $dto = new AssignRoleDTO(
                userId: $userId,
                roleId: $request->input('role_id')
            );

            $this->assignRoleUseCase->execute($dto);

            return response()->json(['message' => 'Role assigned successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
