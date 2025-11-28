<?php

namespace NebulaDesk\Application\DTOs;

class UpdateTicketDTO
{
    public function __construct(
        public readonly int $ticketId,
        public readonly ?string $subject = null,
        public readonly ?string $description = null,
        public readonly ?string $priority = null
    ) {
    }
}
