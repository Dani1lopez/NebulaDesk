<?php

namespace NebulaDesk\Application\UseCases;

use NebulaDesk\Domain\Repositories\OrganizationRepositoryInterface;

class DeleteOrganizationUseCase
{
    public function __construct(
        private OrganizationRepositoryInterface $organizationRepository
    ) {
    }

    public function execute(int $id): bool
    {
        // Verify organization exists
        $organization = $this->organizationRepository->findById($id);

        if (!$organization) {
            throw new \Exception('Organization not found');
        }

        // Soft delete - users and tickets remain intact
        // Eloquent SoftDeletes sets deleted_at timestamp
        // No cascade deletion - data is preserved
        return $this->organizationRepository->delete($id);
    }
}
