<?php

namespace NebulaDesk\Infrastructure\Persistence\Eloquent;

use NebulaDesk\Domain\Entities\Permission;
use NebulaDesk\Domain\Repositories\PermissionRepositoryInterface;
use Illuminate\Support\Facades\DB;

class EloquentPermissionRepository implements PermissionRepositoryInterface
{
    public function create(Permission $permission): Permission
    {
        $id = DB::table('permissions')->insertGetId([
            'name' => $permission->name,
            'description' => $permission->description,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return new Permission($id, $permission->name, $permission->description);
    }

    public function findById(int $id): ?Permission
    {
        $record = DB::table('permissions')->where('id', $id)->first();
        if (!$record) {
            return null;
        }
        return new Permission($record->id, $record->name, $record->description);
    }

    public function findAll(): array
    {
        $records = DB::table('permissions')->get();
        $permissions = [];
        foreach ($records as $record) {
            $permissions[] = new Permission($record->id, $record->name, $record->description);
        }
        return $permissions;
    }

    public function attachToRole(int $roleId, int $permissionId): void
    {
        DB::table('role_permission')->updateOrInsert(
            ['role_id' => $roleId, 'permission_id' => $permissionId],
            ['created_at' => now(), 'updated_at' => now()]
        );
    }

    public function getRolePermissions(int $roleId): array
    {
        $records = DB::table('role_permission')
            ->join('permissions', 'role_permission.permission_id', '=', 'permissions.id')
            ->where('role_permission.role_id', $roleId)
            ->select('permissions.*')
            ->get();

        $permissions = [];
        foreach ($records as $record) {
            $permissions[] = new Permission($record->id, $record->name, $record->description);
        }
        return $permissions;
    }
}
