<?php

namespace NebulaDesk\Domain\Repositories;

use NebulaDesk\Domain\Entities\AuditLog;

interface AuditLogRepositoryInterface
{
    public function create(AuditLog $auditLog): AuditLog;
    /**
     * Retrieve audit logs, optionally filtered by user, entity type, etc.
     */
    public function findAll(array $filters = []): array;
}
