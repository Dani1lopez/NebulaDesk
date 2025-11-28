<?php

namespace NebulaDesk\Application\UseCases;

use NebulaDesk\Application\DTOs\CreateTicketDTO;
use NebulaDesk\Domain\Entities\Ticket;
use NebulaDesk\Domain\Repositories\TicketRepositoryInterface;

class CreateTicketUseCase
{
    public function __construct(
        private TicketRepositoryInterface $ticketRepository
    ) {
    }

    public function execute(CreateTicketDTO $dto): Ticket
    {
        // Calculate SLA
        $sla = \App\Models\SLA::where('priority', $dto->priority)->first();
        $slaDueDate = null;
        if ($sla) {
            $slaDueDate = (new \DateTimeImmutable())->modify("+{$sla->resolution_time_hours} hours");
        }

        $ticket = new Ticket(
            id: null,
            subject: $dto->subject,
            description: $dto->description,
            status: 'open',
            priority: $dto->priority,
            requesterId: $dto->requesterId,
            assigneeId: null,
            organizationId: $dto->organizationId,
            slaDueDate: $slaDueDate,
            slaBreached: false
        );

        return $this->ticketRepository->save($ticket);
    }
}
