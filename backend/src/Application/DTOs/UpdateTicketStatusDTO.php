<?php

namespace NebulaDesk\Application\DTOs;

class UpdateTicketStatusDTO
{
    public function __construct(
        public readonly int $ticketId,
        public readonly string $status // open, in-progress, resolved, closed
    ) {
    }
}
