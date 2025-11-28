<?php

namespace NebulaDesk\Domain\Entities;

class Ticket
{
    public function __construct(
        public ?int $id,
        public string $subject,
        public string $description,
        public string $status, // 'open', 'in_progress', 'closed'
        public string $priority, // 'low', 'medium', 'high', 'critical'
        public int $requesterId,
        public ?int $assigneeId,
        public ?int $organizationId,
        public ?\DateTimeImmutable $slaDueDate = null,
        public bool $slaBreached = false,
        public ?\DateTimeImmutable $createdAt = null,
        public ?\DateTimeImmutable $updatedAt = null,
    ) {
    }
}
