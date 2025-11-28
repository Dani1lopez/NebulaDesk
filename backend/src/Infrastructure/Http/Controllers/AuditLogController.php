<?php

namespace NebulaDesk\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use NebulaDesk\Domain\Repositories\AuditLogRepositoryInterface;
use NebulaDesk\Domain\Entities\AuditLog;

class AuditLogController extends Controller
{
    private AuditLogRepositoryInterface $auditLogRepository;

    public function __construct(AuditLogRepositoryInterface $auditLogRepository)
    {
        $this->auditLogRepository = $auditLogRepository;
    }

    /**
     * List audit logs, optionally filtered by query parameters.
     */
    public function index(): JsonResponse
    {
        $user = request()->user();
        
        // Authorization: Admin/Owner only
        if (!$user->isAdmin() && !$user->isOwner()) {
            return response()->json(['message' => 'You do not have permission to view audit logs.'], 403);
        }

        if (!$user->organization_id) {
             return response()->json(['audit_logs' => []]);
        }

        $filters = [];
        
        // Filter by organization users
        // We need to get all user IDs for this organization
        $orgUserIds = \App\Models\User::where('organization_id', $user->organization_id)->pluck('id')->toArray();
        
        if (empty($orgUserIds)) {
             return response()->json(['audit_logs' => []]);
        }
        
        $filters['user_ids'] = $orgUserIds;

        if (request()->has('user_id')) {
            // Ensure the requested user_id belongs to the org
            $requestedUserId = (int) request('user_id');
            if (in_array($requestedUserId, $orgUserIds)) {
                $filters['user_id'] = $requestedUserId;
            } else {
                // If requesting a user not in org, return empty
                return response()->json(['audit_logs' => []]);
            }
        }
        if (request()->has('entity_type')) {
            $filters['entity_type'] = request('entity_type');
        }
        $logs = $this->auditLogRepository->findAll($filters);
        $data = array_map(function (AuditLog $log) {
            return [
                'id' => $log->getId(),
                'user_id' => $log->getUserId(),
                'action' => $log->getAction(),
                'entity_type' => $log->getEntityType(),
                'entity_id' => $log->getEntityId(),
                'created_at' => $log->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }, $logs);
        return response()->json(['audit_logs' => $data]);
    }
}
