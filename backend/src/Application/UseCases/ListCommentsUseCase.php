<?php

namespace NebulaDesk\Application\UseCases;

use NebulaDesk\Domain\Entities\User;
use NebulaDesk\Domain\Repositories\CommentRepositoryInterface;
use NebulaDesk\Domain\Repositories\TicketRepositoryInterface;
use NebulaDesk\Application\Services\OrganizationGuard;

class ListCommentsUseCase
{
    public function __construct(
        private CommentRepositoryInterface $commentRepository,
        private TicketRepositoryInterface $ticketRepository,
        private OrganizationGuard $organizationGuard
    ) {
    }

    public function execute(int $ticketId, User $user): array
    {
        // Verify ticket exists and user has access
        $ticket = $this->ticketRepository->findById($ticketId);
        if (!$ticket) {
            throw new \Exception('Ticket not found');
        }

        // Validate multi-tenant access
        $this->organizationGuard->ensureSameOrganization($ticket, $user);

        return $this->commentRepository->findByTicketId($ticketId);
    }
}
