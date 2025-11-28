<?php

namespace NebulaDesk\Application\UseCases;

use NebulaDesk\Application\DTOs\CreateRoleDTO;
use NebulaDesk\Domain\Entities\Role;
use NebulaDesk\Domain\Repositories\RoleRepositoryInterface;

class CreateRoleUseCase
{
    private RoleRepositoryInterface $roleRepository;

    public function __construct(RoleRepositoryInterface $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    public function execute(CreateRoleDTO $dto): Role
    {
        // Check if role already exists
        $existing = $this->roleRepository->findByName($dto->name);
        if ($existing) {
            throw new \Exception('Role already exists');
        }

        $role = new Role(0, $dto->name, $dto->description);
        return $this->roleRepository->create($role);
    }
}
