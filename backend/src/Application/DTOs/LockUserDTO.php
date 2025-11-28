<?php

namespace NebulaDesk\Application\DTOs;

class LockUserDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly int $adminId
    ) {
    }
}
