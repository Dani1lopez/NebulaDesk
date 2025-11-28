<?php

namespace NebulaDesk\Application\UseCases;

use NebulaDesk\Domain\Repositories\RoleRepositoryInterface;

class ListRolesUseCase
{
    private RoleRepositoryInterface $roleRepository;

    public function __construct(RoleRepositoryInterface $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    public function execute(): array
    {
        return $this->roleRepository->findAll();
    }
}
