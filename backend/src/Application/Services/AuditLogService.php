<?php

namespace NebulaDesk\Application\Services;

use NebulaDesk\Domain\Repositories\AuditLogRepositoryInterface;
use NebulaDesk\Domain\Entities\AuditLog;
use DateTimeImmutable;

class AuditLogService
{
    private AuditLogRepositoryInterface $auditLogRepository;

    public function __construct(AuditLogRepositoryInterface $auditLogRepository)
    {
        $this->auditLogRepository = $auditLogRepository;
    }

    /**
     * Record an audit log entry.
     */
    public function log(int $userId, string $action, string $entityType, int $entityId): AuditLog
    {
        $auditLog = new AuditLog(
            0, // ID will be set by DB
            $userId,
            $action,
            $entityType,
            $entityId,
            new DateTimeImmutable()
        );
        return $this->auditLogRepository->create($auditLog);
    }
}
