<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Organization;
use Illuminate\Auth\Access\Response;

class OrganizationPolicy
{
    /**
     * Determine whether the user can view any models.
     * All authenticated users can access the list, but filtering happens in the use case
     */
    public function viewAny(User $user): bool
    {
        // Allow all authenticated users - filtering by role happens in ListOrganizationsUseCase
        return true;
    }

    /**
     * Determine whether the user can view the model.
     * User can view their own organization OR admin can view any
     */
    public function view(User $user, Organization $organization): bool
    {
        // Admin can view any organization
        if ($user->isAdmin()) {
            return true;
        }

        // User can view their own organization
        // Cast to int to avoid strict comparison issues
        return (int) $user->organization_id === (int) $organization->id;
    }

    /**
     * Determine whether the user can create models.
     * Only admin can create organizations
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     * Only admin can update ANY organization
     */
    public function update(User $user, Organization $organization): bool
    {
        // Only admins can update any organization
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     * Only admin can delete organizations (soft delete)
     */
    public function delete(User $user, Organization $organization): bool
    {
        // Only admins can delete organizations
        // Additional safety: prevent deleting your own organization
        if ($user->organization_id === $organization->id) {
            return false; // Cannot delete your own organization
        }

        return $user->isAdmin();
    }

    /**
     * Determine whether the user can add users to the organization.
     * Admin can add to any org, owner can add to their own org
     */
    public function addUser(User $user, Organization $organization): bool
    {
        // Admin can add users to any organization
        if ($user->isAdmin()) {
            return true;
        }

        // Owner can add users to their own organization
        if ($user->isOwner() && $user->organization_id === $organization->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Organization $organization): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Organization $organization): bool
    {
        return false; // Force delete is not allowed for organizations
    }
}
