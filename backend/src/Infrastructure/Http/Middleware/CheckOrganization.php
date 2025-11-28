<?php

namespace NebulaDesk\Infrastructure\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to ensure that the authenticated user belongs to the requested organization
 */
class CheckOrganization
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Admins can bypass organization checks for management purposes
        if ($user->isAdmin() && $user->role === 'admin') {
            return $next($request);
        }

        // Check if we have a ticket in the route (for show, update, delete, assign, status operations)
        $ticket = $request->route('id') ? \App\Models\Ticket::find($request->route('id')) : null;
        
        if ($ticket) {
            // Validate that the ticket belongs to the user's organization
            if ($ticket->organization_id != $user->organization_id) {
                return response()->json([
                    'message' => 'You do not have access to this ticket'
                ], 403);
            }
        }

        // Check if organization_id is provided in request (for create operations)
        $requestedOrgId = $request->input('organization_id')
            ?? $request->route('organization_id')
            ?? $request->route('organization'); // Handle route model binding

        // If no specific organization requested, allow (controller will filter by user's org)
        if (!$requestedOrgId) {
            return $next($request);
        }

        // If requestedOrgId is an object (model binding), get the ID
        if (is_object($requestedOrgId)) {
            $requestedOrgId = $requestedOrgId->id;
        }

        // Validate requested organization matches user's organization
        if ($user->organization_id != $requestedOrgId) {
            return response()->json([
                'message' => 'You do not have access to this organization'
            ], 403);
        }

        return $next($request);
    }
}
