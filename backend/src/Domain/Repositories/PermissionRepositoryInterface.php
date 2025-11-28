<?php

namespace NebulaDesk\Domain\Repositories;

use NebulaDesk\Domain\Entities\Permission;

interface PermissionRepositoryInterface
{
    public function create(Permission $permission): Permission;
    public function findById(int $id): ?Permission;
    public function findAll(): array;
    public function attachToRole(int $roleId, int $permissionId): void;
    public function getRolePermissions(int $roleId): array;
}
