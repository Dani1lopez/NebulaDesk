<?php

namespace NebulaDesk\Domain\Entities;

use DateTimeInterface;

class Attachment
{
    public function __construct(
        public readonly int $id,
        public readonly int $ticketId,
        public readonly int $userId,
        public readonly string $filename,
        public readonly string $originalFilename,
        public readonly string $filepath,
        public readonly string $mimetype,
        public readonly int $size,
        public readonly DateTimeInterface $createdAt
    ) {
    }
}
