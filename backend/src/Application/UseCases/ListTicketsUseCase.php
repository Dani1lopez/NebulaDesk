<?php

namespace NebulaDesk\Application\UseCases;

use App\Models\User;
use NebulaDesk\Domain\Repositories\TicketRepositoryInterface;

class ListTicketsUseCase
{
    public function __construct(
        private TicketRepositoryInterface $ticketRepository
    ) {
    }

    public function execute(
        User $user,
        ?string $search = null,
        ?string $status = null,
        ?string $priority = null,
        ?int $assignedTo = null
    ): array {
        // Customers can only see their own tickets
        if ($user->role === 'customer') {
            return $this->ticketRepository->findByCreatorId(
                $user->id,
                $search,
                $status,
                $priority,
                $assignedTo
            );
        }

        // Admin, owner, and agent can see organization tickets
        return $this->ticketRepository->findByOrganizationId(
            $user->organization_id,
            $search,
            $status,
            $priority,
            $assignedTo
        );
    }
}
