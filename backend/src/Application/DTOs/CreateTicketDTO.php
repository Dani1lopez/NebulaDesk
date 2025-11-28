<?php

namespace NebulaDesk\Application\DTOs;

class CreateTicketDTO
{
    public function __construct(
        public string $subject,
        public string $description,
        public string $priority,
        public int $requesterId,
        public ?int $organizationId,
    ) {
    }
}
