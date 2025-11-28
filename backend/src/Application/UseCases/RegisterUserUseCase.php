<?php

namespace NebulaDesk\Application\UseCases;

use Illuminate\Support\Facades\Hash;
use NebulaDesk\Application\DTOs\RegisterUserDTO;
use NebulaDesk\Domain\Entities\User;
use NebulaDesk\Domain\Repositories\UserRepositoryInterface;

class RegisterUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function execute(RegisterUserDTO $dto): User
    {
        if ($this->userRepository->findByEmail($dto->email)) {
            throw new \Exception("User already exists");
        }

        $user = new User(
            id: null,
            name: $dto->name,
            email: $dto->email,
            password: Hash::make($dto->password), // Using Facade for simplicity, could be abstracted
            organizationId: $dto->organizationId ?? null,
            role: $dto->role ?? 'customer'
        );

        return $this->userRepository->save($user);
    }
}
