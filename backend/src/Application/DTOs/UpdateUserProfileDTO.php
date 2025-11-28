<?php

namespace NebulaDesk\Application\DTOs;

class UpdateUserProfileDTO
{
    public function __construct(
        public int $userId,
        public ?string $name = null,
        public ?string $email = null,
        public ?string $avatarPath = null,
    ) {
    }
}
