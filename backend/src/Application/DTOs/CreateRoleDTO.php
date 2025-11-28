<?php

namespace NebulaDesk\Application\DTOs;

class CreateRoleDTO
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $description = null
    ) {
    }
}
