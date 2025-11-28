<?php

namespace NebulaDesk\Application\UseCases;

use NebulaDesk\Domain\Repositories\OrganizationRepositoryInterface;
use NebulaDesk\Domain\Entities\Organization;

class GetOrganizationUseCase
{
    public function __construct(
        private OrganizationRepositoryInterface $organizationRepository
    ) {
    }

    public function execute(int $id): ?Organization
    {
        return $this->organizationRepository->findById($id);
    }
}
