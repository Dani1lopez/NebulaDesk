<?php

namespace NebulaDesk\Domain\Repositories;

use NebulaDesk\Domain\Entities\Organization;

interface OrganizationRepositoryInterface
{
    public function findAll(): array;
    public function findById(int $id): ?Organization;
    public function save(Organization $organization): Organization;
    public function update(Organization $organization): Organization;
    public function delete(int $id): bool;
}
