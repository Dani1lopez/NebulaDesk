<?php

namespace NebulaDesk\Application\UseCases;

use NebulaDesk\Application\DTOs\AssignRoleDTO;
use NebulaDesk\Domain\Repositories\RoleRepositoryInterface;
use NebulaDesk\Domain\Repositories\UserRepositoryInterface;

class AssignRoleUseCase
{
    private RoleRepositoryInterface $roleRepository;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        RoleRepositoryInterface $roleRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->roleRepository = $roleRepository;
        $this->userRepository = $userRepository;
    }

    public function execute(AssignRoleDTO $dto): void
    {
        // Verify user exists
        $user = $this->userRepository->findById($dto->userId);
        if (!$user) {
            throw new \Exception('User not found');
        }

        // Verify role exists
        $role = $this->roleRepository->findById($dto->roleId);
        if (!$role) {
            throw new \Exception('Role not found');
        }

        $this->roleRepository->assignToUser($dto->userId, $dto->roleId);
    }
}
