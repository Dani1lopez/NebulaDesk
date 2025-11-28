<?php

namespace NebulaDesk\Domain\Repositories;

use NebulaDesk\Domain\Entities\Role;

interface RoleRepositoryInterface
{
    public function create(Role $role): Role;
    public function findById(int $id): ?Role;
    public function findByName(string $name): ?Role;
    public function findAll(): array;
    public function assignToUser(int $userId, int $roleId): void;
    public function getUserRoles(int $userId): array;
}
