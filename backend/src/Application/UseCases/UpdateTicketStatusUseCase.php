<?php

namespace NebulaDesk\Application\UseCases;

use NebulaDesk\Application\DTOs\UpdateTicketStatusDTO;
use NebulaDesk\Domain\Entities\User;
use NebulaDesk\Domain\Repositories\TicketRepositoryInterface;
use NebulaDesk\Application\Services\OrganizationGuard;
use Illuminate\Support\Facades\DB;

class UpdateTicketStatusUseCase
{
    private TicketRepositoryInterface $ticketRepository;
    private OrganizationGuard $organizationGuard;

    public function __construct(
        TicketRepositoryInterface $ticketRepository,
        OrganizationGuard $organizationGuard
    ) {
        $this->ticketRepository = $ticketRepository;
        $this->organizationGuard = $organizationGuard;
    }

    public function execute(UpdateTicketStatusDTO $dto, User $user): void
    {
        $ticket = $this->ticketRepository->findById($dto->ticketId);
        if (!$ticket) {
            throw new \Exception('Ticket not found');
        }

        // Validate multi-tenant access
        $this->organizationGuard->ensureSameOrganization($ticket, $user);

        $validStatuses = ['open', 'in-progress', 'resolved', 'closed'];
        if (!in_array($dto->status, $validStatuses)) {
            throw new \Exception('Invalid status');
        }

        $updates = ['status' => $dto->status, 'updated_at' => now()];

        // Check for SLA breach if status is resolved or closed
        if (in_array($dto->status, ['resolved', 'closed'])) {
            // Check if ticket has SLA due date and if we're past it
            if ($ticket->slaDueDate && $ticket->slaDueDate < new \DateTimeImmutable()) {
                $updates['sla_breached'] = true;
            } else {
                // Ticket resolved within SLA time
                $updates['sla_breached'] = false;
            }
        } else {
            // For open/in-progress tickets, check if we're past due date
            // This allows partial detection even before resolution
            if ($ticket->slaDueDate && $ticket->slaDueDate < new \DateTimeImmutable()) {
                $updates['sla_breached'] = true;
            }
        }

        DB::table('tickets')
            ->where('id', $dto->ticketId)
            ->update($updates);
    }
}
