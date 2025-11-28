<?php

namespace NebulaDesk\Application\UseCases;

use NebulaDesk\Domain\Repositories\OrganizationRepositoryInterface;
use NebulaDesk\Domain\Repositories\UserRepositoryInterface;

class ListOrganizationsUseCase
{
    public function __construct(
        private OrganizationRepositoryInterface $organizationRepository,
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function execute(?int $userId = null, ?string $userRole = null): array
    {
        $organizations = $this->organizationRepository->findAll();

        // Admin sees all organizations
        if ($userRole === 'admin') {
            return $organizations;
        }

        // Owner/Agent/Customer see only their own organization
        if ($userId) {
            $user = $this->userRepository->findById($userId);
            if ($user && $user->organizationId) {
                return array_values(array_filter(
                    $organizations,
                    fn($org) => $org->id === $user->organizationId
                ));
            }
        }

        // If not admin and no organization found (or user not found), return empty
        return [];
    }
}
