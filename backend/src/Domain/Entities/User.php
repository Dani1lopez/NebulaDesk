<?php

namespace NebulaDesk\Domain\Entities;

class User
{
    public function __construct(
        public ?int $id,
        public string $name,
        public string $email,
        public string $password,
        public ?int $organizationId,
        public string $role,
        public ?string $avatar = null,
        public bool $isLocked = false,
        public ?\DateTimeImmutable $lockedAt = null,
        public ?int $lockedBy = null,
        public int $failedLoginAttempts = 0,
        public ?\DateTimeImmutable $createdAt = null,
        public ?\DateTimeImmutable $updatedAt = null,
    ) {
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'owner']);
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function canManageUsers(): bool
    {
        return $this->isAdmin();
    }

    public function isAgent(): bool
    {
        return $this->role === 'agent';
    }

    public function lock(int $adminId): void
    {
        $this->isLocked = true;
        $this->lockedAt = new \DateTimeImmutable();
        $this->lockedBy = $adminId;
    }

    public function unlock(): void
    {
        $this->isLocked = false;
        $this->lockedAt = null;
        $this->lockedBy = null;
        $this->failedLoginAttempts = 0;
    }
}
