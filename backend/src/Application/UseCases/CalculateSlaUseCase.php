<?php

namespace NebulaDesk\Application\UseCases;

use NebulaDesk\Application\DTOs\CalculateSlaDTO;
use NebulaDesk\Domain\Repositories\TicketRepositoryInterface;

class CalculateSlaUseCase
{
    private TicketRepositoryInterface $ticketRepository;

    public function __construct(TicketRepositoryInterface $ticketRepository)
    {
        $this->ticketRepository = $ticketRepository;
    }

    public function execute(CalculateSlaDTO $dto): void
    {
        $ticket = $this->ticketRepository->findById($dto->ticketId);
        if (!$ticket) {
            throw new \Exception('Ticket not found');
        }

        // Calculate SLA deadline: created_at + slaHours
        $slaDeadline = $ticket->createdAt->modify("+{$dto->slaHours} hours");

        // Update ticket with SLA deadline
        // Note: We need to extend Ticket entity and repository to support SLA fields
        // For now, we'll use direct DB update via Eloquent model
        \App\Models\Ticket::where('id', $ticket->id)->update([
            'sla_due_date' => $slaDeadline->format('Y-m-d H:i:s')
        ]);
    }
}
