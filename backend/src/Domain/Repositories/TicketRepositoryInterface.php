<?php

namespace NebulaDesk\Domain\Repositories;

use NebulaDesk\Domain\Entities\Ticket;

interface TicketRepositoryInterface
{
    public function save(Ticket $ticket): Ticket;
    public function findById(int $id): ?Ticket;
    public function findByOrganizationId(
        int $organizationId,
        ?string $search = null,
        ?string $status = null,
        ?string $priority = null,
        ?int $assignedTo = null
    ): array;
    public function findByCreatorId(
        int $creatorId,
        ?string $search = null,
        ?string $status = null,
        ?string $priority = null,
        ?int $assignedTo = null
    ): array;
    public function findAll(): array;
    public function update(Ticket $ticket): Ticket;
    public function delete(int $id): bool;
}
