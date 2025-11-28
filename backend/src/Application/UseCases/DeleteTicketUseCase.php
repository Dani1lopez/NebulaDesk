<?php

namespace NebulaDesk\Application\UseCases;

use NebulaDesk\Application\DTOs\DeleteTicketDTO;
use NebulaDesk\Domain\Entities\User;
use NebulaDesk\Domain\Repositories\TicketRepositoryInterface;
use NebulaDesk\Application\Services\OrganizationGuard;

class DeleteTicketUseCase
{
    public function __construct(
        private TicketRepositoryInterface $ticketRepository,
        private OrganizationGuard $organizationGuard
    ) {
    }

    public function execute(DeleteTicketDTO $dto, User $user): bool
    {
        // Verify ticket exists
        $ticket = $this->ticketRepository->findById($dto->ticketId);

        if (!$ticket) {
            throw new \Exception('Ticket not found');
        }

        // Validate multi-tenant access
        $this->organizationGuard->ensureSameOrganization($ticket, $user);

        return $this->ticketRepository->delete($dto->ticketId);
    }
}
