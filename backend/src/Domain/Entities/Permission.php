<?php

namespace NebulaDesk\Domain\Entities;

class Permission
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $description = null
    ) {
    }
}
