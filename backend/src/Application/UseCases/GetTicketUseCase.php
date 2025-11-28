<?php

namespace NebulaDesk\Application\UseCases;

use NebulaDesk\Domain\Entities\Ticket;
use NebulaDesk\Domain\Entities\User;
use NebulaDesk\Domain\Repositories\TicketRepositoryInterface;
use NebulaDesk\Application\Services\OrganizationGuard;

class GetTicketUseCase
{
    public function __construct(
        private TicketRepositoryInterface $ticketRepository,
        private OrganizationGuard $organizationGuard
    ) {
    }

    public function execute(int $id, User $user): ?Ticket
    {
        $ticket = $this->ticketRepository->findById($id);
        
        if (!$ticket) {
            return null;
        }

        // Validate multi-tenant access
        $this->organizationGuard->ensureSameOrganization($ticket, $user);

        return $ticket;
    }
}
