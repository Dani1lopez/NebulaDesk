<?php

namespace NebulaDesk\Infrastructure\Persistence\Eloquent;

use App\Models\Ticket as EloquentTicket;
use NebulaDesk\Domain\Entities\Ticket;
use NebulaDesk\Domain\Repositories\TicketRepositoryInterface;

class EloquentTicketRepository implements TicketRepositoryInterface
{
    public function save(Ticket $ticket): Ticket
    {
        $eloquentTicket = EloquentTicket::updateOrCreate(
            ['id' => $ticket->id],
            [
                'subject' => $ticket->subject,
                'description' => $ticket->description,
                'status' => $ticket->status,
                'priority' => $ticket->priority,
                'requester_id' => $ticket->requesterId,
                'assignee_id' => $ticket->assigneeId,
                'organization_id' => $ticket->organizationId,
                'sla_due_date' => $ticket->slaDueDate,
                'sla_breached' => $ticket->slaBreached,
            ]
        );

        return $this->toDomain($eloquentTicket);
    }

    public function findById(int $id): ?Ticket
    {
        $eloquentTicket = EloquentTicket::find($id);

        if (!$eloquentTicket) {
            return null;
        }

        return $this->toDomain($eloquentTicket);
    }

    public function findByOrganizationId(
        int $organizationId,
        ?string $search = null,
        ?string $status = null,
        ?string $priority = null,
        ?int $assignedTo = null
    ): array {
        $query = EloquentTicket::where('organization_id', $organizationId);

        // Apply search filter (search in subject and description)
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Apply status filter
        if ($status) {
            $query->where('status', $status);
        }

        // Apply priority filter
        if ($priority) {
            $query->where('priority', $priority);
        }

        // Apply assignee_id filter
        if ($assignedTo) {
            $query->where('assignee_id', $assignedTo);
        }

        $eloquentTickets = $query->orderBy('created_at', 'desc')->get();

        return $eloquentTickets->map(fn($ticket) => $this->toDomain($ticket))->toArray();
    }

    public function findByCreatorId(
        int $creatorId,
        ?string $search = null,
        ?string $status = null,
        ?string $priority = null,
        ?int $assignedTo = null
    ): array {
        $query = EloquentTicket::where('requester_id', $creatorId);

        // Apply search filter (search in subject and description)
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Apply status filter
        if ($status) {
            $query->where('status', $status);
        }

        // Apply priority filter
        if ($priority) {
            $query->where('priority', $priority);
        }

        // Apply assignee_id filter
        if ($assignedTo) {
            $query->where('assignee_id', $assignedTo);
        }

        $eloquentTickets = $query->orderBy('created_at', 'desc')->get();

        return $eloquentTickets->map(fn($ticket) => $this->toDomain($ticket))->toArray();
    }

    public function update(Ticket $ticket): Ticket
    {
        return $this->save($ticket);
    }

    public function findAll(): array
    {
        $eloquentTickets = EloquentTicket::orderBy('created_at', 'desc')->get();
        return $eloquentTickets->map(fn($ticket) => $this->toDomain($ticket))->toArray();
    }

    public function delete(int $id): bool
    {
        $eloquentTicket = EloquentTicket::find($id);

        if (!$eloquentTicket) {
            return false;
        }

        return $eloquentTicket->delete();
    }

    private function toDomain(EloquentTicket $eloquentTicket): Ticket
    {
        return new Ticket(
            id: $eloquentTicket->id,
            subject: $eloquentTicket->subject,
            description: $eloquentTicket->description,
            status: $eloquentTicket->status,
            priority: $eloquentTicket->priority,
            requesterId: $eloquentTicket->requester_id,
            assigneeId: $eloquentTicket->assignee_id,
            organizationId: $eloquentTicket->organization_id,
            slaDueDate: $eloquentTicket->sla_due_date ? \DateTimeImmutable::createFromMutable($eloquentTicket->sla_due_date) : null,
            slaBreached: $eloquentTicket->sla_breached ?? false,
            createdAt: \DateTimeImmutable::createFromMutable($eloquentTicket->created_at),
            updatedAt: \DateTimeImmutable::createFromMutable($eloquentTicket->updated_at),
        );
    }
}
