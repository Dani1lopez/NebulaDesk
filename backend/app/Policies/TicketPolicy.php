<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Ticket;
use Illuminate\Auth\Access\Response;

class TicketPolicy
{
    /**
     * Determine whether the user can view any models.
     * Users can list tickets from their organization
     */
    public function viewAny(User $user): bool
    {
        // User must belong to an organization
        return $user->organization_id !== null;
    }

    /**
     * Determine whether the user can view the model.
     * Users can view tickets from their organization
     * Customers can only view their own tickets
     */
    public function view(User $user, Ticket $ticket): bool
    {
        // User must be in the same organization as the ticket
        if ($user->organization_id !== $ticket->organization_id) {
            return false;
        }

        // Admin and owner can view any ticket in their organization
        if ($user->isAdmin()) {
            return true;
        }

        // Agents can view any ticket in their organization
        if ($user->role === 'agent') {
            return true;
        }

        // Customers can only view their own tickets
        if ($user->role === 'customer') {
            return $ticket->requester_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     * Any authenticated user with an organization can create tickets
     */
    public function create(User $user): bool
    {
        // User must belong to an organization to create tickets
        return $user->organization_id !== null;
    }

    /**
     * Determine whether the user can update the model.
     * Ticket creator, assigned agent, owner, or admin can update
     */
    public function update(User $user, Ticket $ticket): bool
    {
        // User must be in the same organization
        if ($user->organization_id !== $ticket->organization_id) {
            return false;
        }

        // Admin and owner can update any ticket in their organization
        if ($user->isAdmin()) {
            return true;
        }

        // Agent can update if assigned or is the creator
        if ($user->role === 'agent') {
            return $ticket->requester_id === $user->id || $ticket->assignee_id === $user->id;
        }

        // Customer can update only their own ticket
        if ($user->role === 'customer') {
            return $ticket->requester_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     * Only admins, owners, and agents can delete tickets
     */
    public function delete(User $user, Ticket $ticket): bool
    {
        // User must be in the same organization
        if ($user->organization_id !== $ticket->organization_id) {
            return false;
        }

        // Admin and owner can delete any ticket in their organization
        if ($user->isAdmin()) {
            return true;
        }

        // Agents can delete tickets in their organization
        if ($user->role === 'agent') {
            return true;
        }

        // Customers cannot delete tickets
        return false;
    }

    /**
     * Determine whether the user can assign tickets.
     * Only admins, owners, and agents can assign tickets
     */
    public function assign(User $user, Ticket $ticket): bool
    {
        // User must be in the same organization
        if ($user->organization_id !== $ticket->organization_id) {
            return false;
        }

        // Admin and owner can assign any ticket
        if ($user->isAdmin()) {
            return true;
        }

        // Agents can also assign tickets
        if ($user->role === 'agent') {
            return true;
        }

        // Customers cannot assign tickets
        return false;
    }

    /**
     * Determine whether the user can update ticket status.
     * Assigned agent, owner, or admin can update status
     */
    public function updateStatus(User $user, Ticket $ticket): bool
    {
        // User must be in the same organization
        if ($user->organization_id !== $ticket->organization_id) {
            return false;
        }

        // Admin and owner can update any ticket status
        if ($user->isAdmin()) {
            return true;
        }

        // Agent can update status if assigned
        if ($user->role === 'agent' && $ticket->assignee_id === $user->id) {
            return true;
        }

        // Customers cannot update status
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Ticket $ticket): bool
    {
        return $user->isAdmin() && $user->organization_id === $ticket->organization_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Ticket $ticket): bool
    {
        return false; // Force delete is not allowed for tickets
    }
}
