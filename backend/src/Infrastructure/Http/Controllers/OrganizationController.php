<?php

namespace NebulaDesk\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use NebulaDesk\Application\DTOs\CreateOrganizationDTO;
use NebulaDesk\Application\UseCases\CreateOrganizationUseCase;

use NebulaDesk\Application\DTOs\InviteUserDTO;
use NebulaDesk\Application\UseCases\InviteUserUseCase;
use NebulaDesk\Application\UseCases\ListOrganizationUsersUseCase;
use NebulaDesk\Application\UseCases\ListOrganizationsUseCase;
use NebulaDesk\Application\UseCases\GetOrganizationUseCase;
use NebulaDesk\Application\UseCases\UpdateOrganizationUseCase;
use NebulaDesk\Application\UseCases\DeleteOrganizationUseCase;
use NebulaDesk\Application\DTOs\UpdateOrganizationDTO;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class OrganizationController extends Controller
{
    use AuthorizesRequests;
    public function __construct(
        private CreateOrganizationUseCase $createOrganizationUseCase,
        private InviteUserUseCase $inviteUserUseCase,
        private ListOrganizationUsersUseCase $listOrganizationUsersUseCase,
        private ListOrganizationsUseCase $listOrganizationsUseCase,
        private GetOrganizationUseCase $getOrganizationUseCase,
        private UpdateOrganizationUseCase $updateOrganizationUseCase,
        private DeleteOrganizationUseCase $deleteOrganizationUseCase
    ) {
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', \App\Models\Organization::class);

        $request->validate([
            'name' => 'required|string|max:255|unique:organizations,name',
            'domain' => 'nullable|string|regex:/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            'is_active' => 'boolean',
        ]);

        $dto = new CreateOrganizationDTO(
            name: $request->name,
            domain: $request->domain,
            isActive: $request->is_active ?? true
        );

        $organization = $this->createOrganizationUseCase->execute($dto);

        return response()->json([
            'message' => 'Organization created successfully',
            'organization' => [
                'id' => $organization->id,
                'name' => $organization->name,
                'domain' => $organization->domain,
                'is_active' => $organization->isActive,
            ]
        ], 201);
    }

    public function inviteUser(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:admin,agent,customer',
        ]);

        // Security: Ensure the authenticated user belongs to the organization they are inviting to
        // For now, we assume the user is inviting to their own organization
        $organizationId = $request->user()->organization_id;

        if (!$organizationId) {
            return response()->json(['message' => 'User does not belong to an organization'], 400);
        }

        // Authorization: Check if user can update this organization (which implies managing users)
        // We use the Eloquent model for policy check
        $eloquentOrg = \App\Models\Organization::find($organizationId);
        if ($eloquentOrg) {
            $this->authorize('update', $eloquentOrg);
        }

        $dto = new InviteUserDTO(
            name: $request->name,
            email: $request->email,
            organizationId: $organizationId,
            role: $request->role
        );

        $user = $this->inviteUserUseCase->execute($dto);

        return response()->json([
            'message' => 'User invited successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ]
        ], 201);
    }

    public function listUsers(Request $request): JsonResponse
    {
        try {
            $organizationId = $request->user()->organization_id;

            if (!$organizationId) {
                // If user has no organization, return empty list instead of error
                return response()->json(['users' => []]);
            }

            // Authorization check
            $eloquentOrg = \App\Models\Organization::find($organizationId);
            
            // If organization exists but soft deleted or not found
            if (!$eloquentOrg) {
                 return response()->json(['users' => []]);
            }

            $this->authorize('view', $eloquentOrg);

            $users = $this->listOrganizationUsersUseCase->execute($organizationId);

            $data = array_map(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'avatar' => $user->avatar, // Include avatar in response
            ], $users);

            return response()->json(['users' => $data]);
        } catch (\Exception $e) {
            // Log error if needed, but return empty list or specific error to avoid 500
            // For now, returning empty list is safer for the frontend
            return response()->json(['users' => [], 'error' => $e->getMessage()], 422);
        }
    }

    /**
     * List all organizations
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\Organization::class);

        $user = $request->user();
        
        // If user is not admin and has no organization, they might see nothing or just their own (which is none)
        // The use case should handle this, but let's be safe
        
        try {
            $organizations = $this->listOrganizationsUseCase->execute($user->id, $user->role);
        } catch (\Exception $e) {
            // Fallback to empty list if something goes wrong in use case
            return response()->json(['organizations' => []]);
        }

        $data = array_map(function($org) {
            // Load Eloquent model to get counts
            $eloquentOrg = \App\Models\Organization::find($org->id);
            
            return [
                'id' => $org->id,
                'name' => $org->name,
                'domain' => $org->domain,
                'is_active' => $org->isActive,
                'created_at' => $org->createdAt->format('Y-m-d H:i:s'),
                'users_count' => $eloquentOrg ? $eloquentOrg->users()->count() : 0,
                'tickets_count' => $eloquentOrg ? $eloquentOrg->tickets()->count() : 0,
            ];
        }, $organizations);

        return response()->json(['organizations' => $data]);
    }

    /**
     * Show a specific organization
     */
    public function show(int $id): JsonResponse
    {
        $organization = $this->getOrganizationUseCase->execute($id);

        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        // Authorization check
        $eloquentOrg = \App\Models\Organization::find($id);
        $this->authorize('view', $eloquentOrg);

        // Load Eloquent model for counts
        $eloquentOrg = \App\Models\Organization::find($id);
        
        return response()->json([
            'organization' => [
                'id' => $organization->id,
                'name' => $organization->name,
                'domain' => $organization->domain,
                'is_active' => $organization->isActive,
                'created_at' => $organization->createdAt->format('Y-m-d H:i:s'),
                'users_count' => $eloquentOrg ? $eloquentOrg->users()->count() : 0,
                'tickets_count' => $eloquentOrg ? $eloquentOrg->tickets()->count() : 0,
            ]
        ]);
    }

    /**
     * Update an organization
     */
    public function update(Request $request, int $id): JsonResponse
    {
        // Authorization check
        $eloquentOrg = \App\Models\Organization::find($id);
        if (!$eloquentOrg) {
            return response()->json(['message' => 'Organization not found'], 404);
        }
        $this->authorize('update', $eloquentOrg);

        $request->validate([
            'name' => 'required|string|max:255|unique:organizations,name,' . $id,
            'domain' => 'nullable|string|regex:/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            'is_active' => 'boolean',
        ]);

        try {
            $dto = new UpdateOrganizationDTO(
                organizationId: $id,
                name: $request->input('name'),
                domain: $request->input('domain'),
                isActive: $request->input('is_active')
            );

            $organization = $this->updateOrganizationUseCase->execute($dto);

            return response()->json([
                'message' => 'Organization updated successfully',
                'organization' => [
                    'id' => $organization->id,
                    'name' => $organization->name,
                    'domain' => $organization->domain,
                    'is_active' => $organization->isActive,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Delete an organization
     */
    public function destroy(int $id): JsonResponse
    {
        // Authorization check
        $eloquentOrg = \App\Models\Organization::find($id);
        if (!$eloquentOrg) {
            return response()->json(['message' => 'Organization not found'], 404);
        }
        $this->authorize('delete', $eloquentOrg);

        try {
            $this->deleteOrganizationUseCase->execute($id);
            return response()->json(['message' => 'Organization deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
