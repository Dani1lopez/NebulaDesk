<?php

namespace NebulaDesk\Application\DTOs;

class UpdateOrganizationDTO
{
    public function __construct(
        public readonly int $organizationId,
        public readonly ?string $name = null,
        public readonly ?string $domain = null,
        public readonly ?bool $isActive = null,
    ) {
    }
}
