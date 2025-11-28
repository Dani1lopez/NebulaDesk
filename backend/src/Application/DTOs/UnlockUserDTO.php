<?php

namespace NebulaDesk\Application\DTOs;

class UnlockUserDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly int $adminId
    ) {
    }
}
