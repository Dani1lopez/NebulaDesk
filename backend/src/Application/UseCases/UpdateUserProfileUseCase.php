<?php

namespace NebulaDesk\Application\UseCases;

use NebulaDesk\Application\DTOs\UpdateUserProfileDTO;
use NebulaDesk\Domain\Entities\User;
use NebulaDesk\Domain\Repositories\UserRepositoryInterface;

class UpdateUserProfileUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function execute(UpdateUserProfileDTO $dto): User
    {
        $user = $this->userRepository->findById($dto->userId);

        if (!$user) {
            throw new \Exception("User not found");
        }

        // Update fields if provided
        if ($dto->name) {
            $user->name = $dto->name;
        }
        if ($dto->email) {
            $user->email = $dto->email;
        }
        if ($dto->avatarPath) {
            $user->avatar = $dto->avatarPath;
        }

        return $this->userRepository->save($user);
    }
}
