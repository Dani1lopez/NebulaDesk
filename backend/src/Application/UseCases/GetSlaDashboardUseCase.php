<?php

namespace NebulaDesk\Application\UseCases;

use NebulaDesk\Domain\Repositories\TicketRepositoryInterface;
use NebulaDesk\Domain\Repositories\UserRepositoryInterface;

class GetSlaDashboardUseCase
{
    public function __construct(
        private TicketRepositoryInterface $ticketRepository,
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function execute(int $userId): array
    {
        try {
            $user = $this->userRepository->findById($userId);
            if (!$user) {
                throw new \Exception('User not found');
            }

            // Determine which tickets to fetch based on role
            $tickets = [];
            if ($user->role === 'admin') {
                // Admin sees all tickets from all organizations
                $tickets = $this->ticketRepository->findAll();
            } elseif (in_array($user->role, ['owner', 'agent'])) {
                // Owner and Agent see tickets from their organization
                if ($user->organizationId) {
                    $tickets = $this->ticketRepository->findByOrganizationId($user->organizationId);
                }
            } elseif ($user->role === 'customer') {
                // Customer sees only their own tickets
                $tickets = $this->ticketRepository->findByCreatorId($userId);
            }

            // Filter tickets that have SLA due date
            $slaTickets = array_filter($tickets, fn($t) => $t->slaDueDate !== null);

            // Calculate stats
            $total = count($slaTickets);
            $breached = count(array_filter($slaTickets, fn($t) => $t->slaBreached));
            $onTrack = $total - $breached;

            // Map to response format
            $mappedTickets = array_map(fn($t) => [
                'id' => $t->id,
                'identifier' => "TCK-{$t->id}",
                'subject' => $t->subject,
                'status' => $t->status,
                'priority' => $t->priority,
                'created_at' => $t->createdAt?->format('Y-m-d H:i:s'),
                'sla_due_date' => $t->slaDueDate?->format('Y-m-d H:i:s'),
                'sla_breached' => $t->slaBreached,
            ], $slaTickets);

            return [
                'tickets' => array_values($mappedTickets),
                'stats' => [
                    'total' => $total,
                    'breached' => $breached,
                    'on_track' => $onTrack,
                ]
            ];
        } catch (\Exception $e) {
            // Log error but return empty data instead of crashing
            \Illuminate\Support\Facades\Log::error('SLA Dashboard Error: ' . $e->getMessage());
            
            return [
                'tickets' => [],
                'stats' => [
                    'total' => 0,
                    'breached' => 0,
                    'on_track' => 0,
                ]
            ];
        }
    }
}
