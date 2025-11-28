<?php

namespace NebulaDesk\Application\DTOs;

class CalculateSlaDTO
{
    public function __construct(
        public readonly int $ticketId,
        public readonly int $slaHours = 24 // Default 24 hours
    ) {
    }
}
