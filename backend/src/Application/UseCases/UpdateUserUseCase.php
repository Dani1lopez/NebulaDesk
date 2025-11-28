<?php

namespace NebulaDesk\Application\UseCases;

use NebulaDesk\Application\DTOs\UpdateUserDTO;
use NebulaDesk\Domain\Repositories\UserRepositoryInterface;
use NebulaDesk\Domain\Entities\User;
use Illuminate\Support\Facades\Hash;

class UpdateUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function execute(UpdateUserDTO $dto): User
    {
        // Find the existing user
        $user = $this->userRepository->findById($dto->userId);

        if (!$user) {
            throw new \Exception('User not found');
        }

        // Prepare password (hash if provided, otherwise keep existing)
        $password = $user->password;
        if ($dto->password) {
            $password = Hash::make($dto->password);
        }

        // Create updated user with new values
        $updatedUser = new User(
            id: $user->id,
            name: $dto->name ?? $user->name,
            email: $dto->email ?? $user->email,
            password: $password,
            organizationId: $user->organizationId, // Cannot change organization
            role: $dto->role ?? $user->role,
            createdAt: $user->createdAt,
            updatedAt: new \DateTimeImmutable()
        );

        return $this->userRepository->update($updatedUser);
    }
}
