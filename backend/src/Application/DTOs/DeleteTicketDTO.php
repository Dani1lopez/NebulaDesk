<?php

namespace NebulaDesk\Application\DTOs;

class DeleteTicketDTO
{
    public function __construct(
        public readonly int $ticketId
    ) {
    }
}
