<?php

namespace NebulaDesk\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use NebulaDesk\Application\UseCases\GetSlaDashboardUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SlaController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private GetSlaDashboardUseCase $getSlaDashboardUseCase
    ) {
    }

    public function dashboard(Request $request): JsonResponse
    {
        try {
            // Check if user has permission to view SLA dashboard
            // SLA Dashboard is restricted to Admin and Owner
            $user = $request->user();
            if (!$user->isAdmin() && !$user->isOwner()) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'You do not have permission to view the SLA dashboard.'
                ], 403);
            }

            $stats = $this->getSlaDashboardUseCase->execute($request->user()->id);

            return response()->json($stats);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'You do not have permission to view the SLA dashboard.'
            ], 403);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('SLA Dashboard Controller Error: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Error fetching SLA dashboard',
                'message' => 'An error occurred while fetching SLA data.'
            ], 400);
        }
    }
}
