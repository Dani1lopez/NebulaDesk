<?php

namespace NebulaDesk\Application\DTOs;

class AssignTicketDTO
{
    public function __construct(
        public readonly int $ticketId,
        public readonly int $assignedTo
    ) {
    }
}
