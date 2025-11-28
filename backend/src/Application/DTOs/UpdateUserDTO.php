<?php

namespace NebulaDesk\Application\DTOs;

class UpdateUserDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly ?string $name = null,
        public readonly ?string $email = null,
        public readonly ?string $role = null,
        public readonly ?string $password = null
    ) {
    }
}
