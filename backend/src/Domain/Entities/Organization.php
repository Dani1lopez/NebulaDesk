<?php

namespace NebulaDesk\Domain\Entities;

use DateTimeImmutable;

class Organization
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $domain = null,
        public readonly bool $isActive = true,
        public readonly DateTimeImmutable $createdAt,
        public readonly DateTimeImmutable $updatedAt,
        public readonly ?DateTimeImmutable $deletedAt = null,
    ) {
    }
}
