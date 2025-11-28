<?php

namespace NebulaDesk\Infrastructure\Persistence\Eloquent;

use App\Models\User as EloquentUser;
use NebulaDesk\Domain\Entities\User;
use NebulaDesk\Domain\Repositories\UserRepositoryInterface;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function findByEmail(string $email): ?User
    {
        $eloquentUser = EloquentUser::where('email', $email)->first();

        if (!$eloquentUser) {
            return null;
        }

        return $this->toDomain($eloquentUser);
    }

    public function findById(int $id): ?User
    {
        $eloquentUser = EloquentUser::find($id);

        if (!$eloquentUser) {
            return null;
        }

        return $this->toDomain($eloquentUser);
    }

    public function save(User $user): User
    {
        $eloquentUser = EloquentUser::updateOrCreate(
            ['id' => $user->id],
            [
                'name' => $user->name,
                'email' => $user->email,
                'password' => $user->password,
                'organization_id' => $user->organizationId,
                'role' => $user->role,
                'avatar' => $user->avatar,
            ]
        );

        return $this->toDomain($eloquentUser);
    }

    public function findByOrganizationId(int $organizationId): array
    {
        $eloquentUsers = EloquentUser::where('organization_id', $organizationId)->get();

        return $eloquentUsers->map(fn($user) => $this->toDomain($user))->toArray();
    }

    public function update(User $user): User
    {
        return $this->save($user);
    }

    public function delete(int $id): bool
    {
        $eloquentUser = EloquentUser::find($id);

        if (!$eloquentUser) {
            return false;
        }

        return $eloquentUser->delete();
    }

    public function findAll(): array
    {
        $eloquentUsers = EloquentUser::all();
        return $eloquentUsers->map(fn($user) => $this->toDomain($user))->toArray();
    }

    public function lockUser(int $userId, int $adminId): void
    {
        $eloquentUser = EloquentUser::find($userId);
        if ($eloquentUser) {
            $eloquentUser->lock($adminId);
        }
    }

    public function unlockUser(int $userId): void
    {
        $eloquentUser = EloquentUser::find($userId);
        if ($eloquentUser) {
            $eloquentUser->unlock();
        }
    }

    public function incrementFailedAttempts(int $userId): void
    {
        $eloquentUser = EloquentUser::find($userId);
        if ($eloquentUser) {
            $eloquentUser->failed_login_attempts = ($eloquentUser->failed_login_attempts ?? 0) + 1;
            $eloquentUser->save();
        }
    }

    public function resetFailedAttempts(int $userId): void
    {
        $eloquentUser = EloquentUser::find($userId);
        if ($eloquentUser) {
            $eloquentUser->failed_login_attempts = 0;
            $eloquentUser->locked_at = null;
            $eloquentUser->save();
        }
    }

    public function autoLockUser(int $userId): void
    {
        $eloquentUser = EloquentUser::find($userId);
        if ($eloquentUser) {
            $eloquentUser->is_locked = true;
            $eloquentUser->locked_at = now();
            $eloquentUser->save();
        }
    }

    private function toDomain(EloquentUser $eloquentUser): User
    {
        return new User(
            id: $eloquentUser->id,
            name: $eloquentUser->name,
            email: $eloquentUser->email,
            password: $eloquentUser->password,
            organizationId: $eloquentUser->organization_id,
            role: $eloquentUser->role,
            avatar: $eloquentUser->avatar,
            isLocked: $eloquentUser->is_locked ?? false,
            lockedAt: $eloquentUser->locked_at ? \DateTimeImmutable::createFromMutable($eloquentUser->locked_at) : null,
            lockedBy: $eloquentUser->locked_by,
            failedLoginAttempts: $eloquentUser->failed_login_attempts ?? 0,
            createdAt: $eloquentUser->created_at ? \DateTimeImmutable::createFromMutable($eloquentUser->created_at) : new \DateTimeImmutable(),
            updatedAt: $eloquentUser->updated_at ? \DateTimeImmutable::createFromMutable($eloquentUser->updated_at) : new \DateTimeImmutable(),
        );
    }
}
