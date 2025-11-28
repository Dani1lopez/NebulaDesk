<?php

namespace NebulaDesk\Application\UseCases;

use NebulaDesk\Domain\Repositories\UserRepositoryInterface;

class ListOrganizationUsersUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function execute(int $organizationId): array
    {
        // We need to add findByOrganizationId to UserRepositoryInterface
        // Since we are using EloquentUserRepository, we can implement it there.
        // But first we need to update the interface.
        return $this->userRepository->findByOrganizationId($organizationId);
    }
}
