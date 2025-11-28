<?php

namespace NebulaDesk\Domain\Entities;

class Comment
{
    public function __construct(
        public ?int $id,
        public string $content,
        public int $ticketId,
        public int $userId,
        public ?\DateTimeImmutable $createdAt = null,
        public ?\DateTimeImmutable $updatedAt = null,
    ) {
    }
}
