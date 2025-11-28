<?php

namespace NebulaDesk\Application\UseCases;

use NebulaDesk\Application\DTOs\CreateOrganizationDTO;
use NebulaDesk\Domain\Entities\Organization;
use NebulaDesk\Domain\Repositories\OrganizationRepositoryInterface;

class CreateOrganizationUseCase
{
    public function __construct(
        private OrganizationRepositoryInterface $organizationRepository
    ) {
    }

    public function execute(CreateOrganizationDTO $dto): Organization
    {
        $now = new \DateTimeImmutable();
        
        $organization = new Organization(
            id: 0, // Will be set by the repository after saving
            name: $dto->name,
            domain: $dto->domain ?? null,
            isActive: true,
            createdAt: $now,
            updatedAt: $now
        );

        return $this->organizationRepository->save($organization);
    }
}
