<?php

namespace NebulaDesk\Application\DTOs;

class CreateOrganizationDTO
{
    public function __construct(
        public string $name,
        public ?string $domain = null,
        public bool $isActive = true,
    ) {
    }
}
