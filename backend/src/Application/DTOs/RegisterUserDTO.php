<?php

namespace NebulaDesk\Application\DTOs;

class RegisterUserDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public ?string $organizationName = null,
        public ?int $organizationId = null,
        public ?string $role = null,
    ) {
    }
}
