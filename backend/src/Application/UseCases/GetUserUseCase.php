<?php

namespace NebulaDesk\Application\UseCases;

use NebulaDesk\Domain\Repositories\UserRepositoryInterface;
use NebulaDesk\Domain\Entities\User;

class GetUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function execute(int $id): ?User
    {
        return $this->userRepository->findById($id);
    }
}
