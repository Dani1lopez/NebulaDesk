<?php

namespace NebulaDesk\Application\UseCases;

use NebulaDesk\Application\DTOs\UpdateTicketDTO;
use NebulaDesk\Domain\Repositories\TicketRepositoryInterface;
use NebulaDesk\Domain\Entities\Ticket;
use NebulaDesk\Domain\Entities\User;
use NebulaDesk\Application\Services\OrganizationGuard;

class UpdateTicketUseCase
{
    public function __construct(
        private TicketRepositoryInterface $ticketRepository,
        private OrganizationGuard $organizationGuard
    ) {
    }

    public function execute(UpdateTicketDTO $dto, User $user): Ticket
    {
        // Find the existing ticket
        $ticket = $this->ticketRepository->findById($dto->ticketId);

        if (!$ticket) {
            throw new \Exception('Ticket not found');
        }

        // Validate multi-tenant access
        $this->organizationGuard->ensureSameOrganization($ticket, $user);

        // Check for SLA breach if status is changing to resolved or closed
        // Note: Status update logic might be here or in a separate use case. 
        // Assuming status is NOT updated here based on the code (it uses $ticket->status), 
        // but if we were updating status, we would check here.
        // However, the user request says "Al actualizar estado de un ticket".
        // Let's check if UpdateTicketDTO has status.
        // The current code shows: status: $ticket->status. So status is NOT updated here.
        // It seems status is updated via UpdateTicketStatusUseCase (implied by comment).
        // I should check if UpdateTicketStatusUseCase exists.
        
        $updatedTicket = new Ticket(
            id: $ticket->id,
            subject: $dto->subject ?? $ticket->subject,
            description: $dto->description ?? $ticket->description,
            status: $ticket->status, 
            priority: $dto->priority ?? $ticket->priority,
            requesterId: $ticket->requesterId,
            assigneeId: $ticket->assigneeId,
            organizationId: $ticket->organizationId,
            slaDueDate: $ticket->slaDueDate,
            slaBreached: $ticket->slaBreached,
            createdAt: $ticket->createdAt,
            updatedAt: new \DateTimeImmutable()
        );

        return $this->ticketRepository->update($updatedTicket);
    }
}
