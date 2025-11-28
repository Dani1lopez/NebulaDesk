<?php

namespace NebulaDesk\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use NebulaDesk\Application\Services\MetricsService;

class DashboardController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public function __construct(
        private MetricsService $metricsService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $organizationId = $user->organization_id;

        if (!$organizationId) {
            return response()->json(['message' => 'User does not belong to an organization'], 403);
        }

        // Authorization: Ensure user can view their organization
        // We use the Organization model for policy check
        $eloquentOrg = \App\Models\Organization::find($organizationId);
        if (!$eloquentOrg) {
             return response()->json(['message' => 'Organization not found'], 404);
        }
        
        $this->authorize('view', $eloquentOrg);

        // Only Admin and Owner should see full metrics? 
        // User request says: "Recommended: admin + owner ONLY."
        // So let's enforce that.
        if (!$user->isAdmin() && !$user->isOwner()) {
             return response()->json(['message' => 'You do not have permission to view dashboard metrics.'], 403);
        }

        $metrics = $this->metricsService->getDashboardMetrics($organizationId);

        return response()->json($metrics);
    }
}
