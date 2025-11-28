<?php

namespace NebulaDesk\Application\UseCases;

use NebulaDesk\Application\DTOs\UpdateOrganizationDTO;
use NebulaDesk\Domain\Repositories\OrganizationRepositoryInterface;
use NebulaDesk\Domain\Entities\Organization;

class UpdateOrganizationUseCase
{
    public function __construct(
        private OrganizationRepositoryInterface $organizationRepository
    ) {
    }

    public function execute(UpdateOrganizationDTO $dto): Organization
    {
        // Find the existing organization
        $organization = $this->organizationRepository->findById($dto->organizationId);

        if (!$organization) {
            throw new \Exception('Organization not found');
        }

        // Create updated organization with new values
        $updatedOrganization = new Organization(
            id: $organization->id,
            name: $dto->name ?? $organization->name,
            createdAt: $organization->createdAt,
            updatedAt: new \DateTimeImmutable()
        );

        return $this->organizationRepository->update($updatedOrganization);
    }
}
