<?php

namespace NebulaDesk\Infrastructure\Persistence\Eloquent;

use NebulaDesk\Domain\Entities\AuditLog;
use NebulaDesk\Domain\Repositories\AuditLogRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EloquentAuditLogRepository implements AuditLogRepositoryInterface
{
    public function create(AuditLog $auditLog): AuditLog
    {
        $model = new class extends Model {
            protected $table = 'audit_logs';
            public $timestamps = false;
            protected $guarded = [];
        };
        $saved = $model->create([
            'user_id' => $auditLog->getUserId(),
            'action' => $auditLog->getAction(),
            'entity_type' => $auditLog->getEntityType(),
            'entity_id' => $auditLog->getEntityId(),
            'created_at' => $auditLog->getCreatedAt()->format('Y-m-d H:i:s'),
        ]);
        // Return a new domain entity with ID from DB
        return new AuditLog(
            $saved->id,
            $saved->user_id,
            $saved->action,
            $saved->entity_type,
            $saved->entity_id,
            new \DateTimeImmutable($saved->created_at)
        );
    }

    public function findAll(array $filters = []): array
    {
        $query = DB::table('audit_logs');
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        if (isset($filters['user_ids']) && is_array($filters['user_ids'])) {
            $query->whereIn('user_id', $filters['user_ids']);
        }
        if (isset($filters['entity_type'])) {
            $query->where('entity_type', $filters['entity_type']);
        }
        $records = $query->orderByDesc('created_at')->get();
        $logs = [];
        foreach ($records as $record) {
            $logs[] = new AuditLog(
                $record->id,
                $record->user_id,
                $record->action,
                $record->entity_type,
                $record->entity_id,
                new \DateTimeImmutable($record->created_at)
            );
        }
        return $logs;
    }
}
