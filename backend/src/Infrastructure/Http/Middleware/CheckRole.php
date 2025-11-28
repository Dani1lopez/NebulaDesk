<?php

namespace NebulaDesk\Infrastructure\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Check if user has one of the required roles
        // We can check against the user's role property
        // or use the isAdmin/isOwner helpers if applicable

        $userRole = $request->user()->role;

        // Admin/Owner usually has access to everything, but let's be explicit
        // If the route requires 'admin', only admin/owner can access
        // If the route requires 'agent', admin/owner/agent can access

        foreach ($roles as $role) {
            if ($userRole === $role) {
                return $next($request);
            }

            // Hierarchy check
            if ($role === 'agent' && ($userRole === 'admin' || $userRole === 'owner')) {
                return $next($request);
            }

            if ($role === 'admin' && $userRole === 'owner') {
                return $next($request);
            }
        }

        return response()->json(['message' => 'Unauthorized. You do not have the required role.'], 403);
    }
}
