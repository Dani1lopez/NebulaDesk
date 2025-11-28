<?php

namespace NebulaDesk\Application\DTOs;

class AssignRoleDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly int $roleId
    ) {
    }
}
