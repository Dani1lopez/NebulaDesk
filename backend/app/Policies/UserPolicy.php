<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     * Only admin/owner can list all users
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the model.
     * User can view their own profile OR admin can view any
     */
    public function view(User $user, User $model): bool
    {
        // User can always view their own profile
        if ($user->id === $model->id) {
            return true;
        }

        // Admin can view any user
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can create models.
     * Only admin/owner can create users
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     * User can update their own profile OR admin can update any
     */
    public function update(User $user, User $model): bool
    {
        // User can update their own profile
        if ($user->id === $model->id) {
            return true;
        }

        // Admin can update any user
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     * Only admin/owner can delete users (cannot delete self)
     */
    public function delete(User $user, User $model): bool
    {
        // Cannot delete yourself
        if ($user->id === $model->id) {
            return false;
        }

        // Only admin can delete users
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can lock the model.
     * Only admin/owner can lock users (cannot lock self)
     */
    public function lock(User $user, User $model): bool
    {
        // Cannot lock yourself
        if ($user->id === $model->id) {
            return false;
        }

        // Only admin can lock users
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can unlock the model.
     * Only admin/owner can unlock users
     */
    public function unlock(User $user, User $model): bool
    {
        // Only admin can unlock users
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return false; // Force delete is not allowed
    }
}
