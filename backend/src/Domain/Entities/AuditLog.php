<?php

namespace NebulaDesk\Domain\Entities;

use DateTimeImmutable;

class AuditLog
{
    private int $id;
    private int $userId;
    private string $action;
    private string $entityType;
    private int $entityId;
    private DateTimeImmutable $createdAt;

    public function __construct(
        int $id,
        int $userId,
        string $action,
        string $entityType,
        int $entityId,
        DateTimeImmutable $createdAt
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->action = $action;
        $this->entityType = $entityType;
        $this->entityId = $entityId;
        $this->createdAt = $createdAt;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getEntityType(): string
    {
        return $this->entityType;
    }

    public function getEntityId(): int
    {
        return $this->entityId;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
