<?php

namespace NebulaDesk\Application\UseCases;

use NebulaDesk\Application\DTOs\InviteUserDTO;
use NebulaDesk\Domain\Entities\User;
use NebulaDesk\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class InviteUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function execute(InviteUserDTO $dto): User
    {
        // In a real app, we would send an email with a link to set password.
        // For this MVP, we create the user with a default password.

        // Check if user already exists (should be handled by repository or validation, but good to check)
        // For now, we assume validation is done in controller.

        $user = new User(
            id: null,
            name: $dto->name,
            email: $dto->email,
            password: Hash::make('password'), // Default password
            organizationId: $dto->organizationId,
            role: $dto->role
        );

        return $this->userRepository->save($user);
    }
}
