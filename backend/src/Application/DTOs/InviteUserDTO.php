<?php

namespace NebulaDesk\Application\DTOs;

class InviteUserDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public int $organizationId,
        public string $role = 'agent', // Default role
    ) {
    }
}
