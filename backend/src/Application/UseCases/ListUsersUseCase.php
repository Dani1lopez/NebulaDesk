<?php

namespace NebulaDesk\Application\UseCases;

use NebulaDesk\Domain\Repositories\UserRepositoryInterface;
use NebulaDesk\Domain\Entities\User;

class ListUsersUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    /**
     * List all users (for admin only)
     * 
     * @return User[]
     */
    public function execute(): array
    {
        return $this->userRepository->findAll();
    }
}
