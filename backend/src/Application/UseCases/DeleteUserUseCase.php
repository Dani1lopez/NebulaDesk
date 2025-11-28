<?php

namespace NebulaDesk\Application\UseCases;

use NebulaDesk\Domain\Repositories\UserRepositoryInterface;

class DeleteUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function execute(int $id): bool
    {
        // Verify user exists
        $user = $this->userRepository->findById($id);

        if (!$user) {
            throw new \Exception('User not found');
        }

        return $this->userRepository->delete($id);
    }
}
