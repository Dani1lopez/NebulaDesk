<?php

namespace NebulaDesk\Application\Services;

use NebulaDesk\Domain\Repositories\TicketRepositoryInterface;

class MetricsService
{
    public function __construct(
        private TicketRepositoryInterface $ticketRepository
    ) {
    }

    public function getDashboardMetrics(int $organizationId): array
    {
        // In a real app, we would add optimized queries to the repository
        // For now, we fetch all and count (not efficient for large scale, but fine for MVP)
        $tickets = $this->ticketRepository->findByOrganizationId($organizationId);

        $total = count($tickets);
        $open = count(array_filter($tickets, fn($t) => $t->status === 'open'));
        $closed = count(array_filter($tickets, fn($t) => $t->status === 'closed'));

        // Calculate pending actions (e.g., high priority open tickets)
        $pending = count(array_filter($tickets, fn($t) => $t->status === 'open' && in_array($t->priority, ['high', 'critical'])));

        return [
            'total_tickets' => $total,
            'open_tickets' => $open,
            'closed_tickets' => $closed,
            'pending_actions' => $pending,
        ];
    }
}
