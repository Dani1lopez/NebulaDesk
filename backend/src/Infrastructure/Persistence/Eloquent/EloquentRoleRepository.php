<?php

namespace NebulaDesk\Infrastructure\Persistence\Eloquent;

use NebulaDesk\Domain\Entities\Role;
use NebulaDesk\Domain\Repositories\RoleRepositoryInterface;
use Illuminate\Support\Facades\DB;

class EloquentRoleRepository implements RoleRepositoryInterface
{
    public function create(Role $role): Role
    {
        $id = DB::table('roles')->insertGetId([
            'name' => $role->name,
            'description' => $role->description,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return new Role($id, $role->name, $role->description);
    }

    public function findById(int $id): ?Role
    {
        $record = DB::table('roles')->where('id', $id)->first();
        if (!$record) {
            return null;
        }
        return new Role($record->id, $record->name, $record->description);
    }

    public function findByName(string $name): ?Role
    {
        $record = DB::table('roles')->where('name', $name)->first();
        if (!$record) {
            return null;
        }
        return new Role($record->id, $record->name, $record->description);
    }

    public function findAll(): array
    {
        $records = DB::table('roles')->get();
        $roles = [];
        foreach ($records as $record) {
            $roles[] = new Role($record->id, $record->name, $record->description);
        }
        return $roles;
    }

    public function assignToUser(int $userId, int $roleId): void
    {
        DB::table('user_roles')->updateOrInsert(
            ['user_id' => $userId, 'role_id' => $roleId],
            ['created_at' => now(), 'updated_at' => now()]
        );
    }

    public function getUserRoles(int $userId): array
    {
        $records = DB::table('user_roles')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->where('user_roles.user_id', $userId)
            ->select('roles.*')
            ->get();

        $roles = [];
        foreach ($records as $record) {
            $roles[] = new Role($record->id, $record->name, $record->description);
        }
        return $roles;
    }
}
