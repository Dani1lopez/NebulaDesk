<?php

namespace NebulaDesk\Domain\Repositories;

use NebulaDesk\Domain\Entities\User;

interface UserRepositoryInterface
{
    public function findByEmail(string $email): ?User;
    public function findById(int $id): ?User;
    public function save(User $user): User;
    public function update(User $user): User;
    public function delete(int $id): bool;
    public function findByOrganizationId(int $organizationId): array;
    public function findAll(): array;
    public function lockUser(int $userId, int $adminId): void;
    public function unlockUser(int $userId): void;
    
    // Auto-lock support methods
    public function incrementFailedAttempts(int $userId): void;
    public function resetFailedAttempts(int $userId): void;
    public function autoLockUser(int $userId): void;
}
