<?php

namespace NebulaDesk\Application\UseCases;

use NebulaDesk\Application\DTOs\AssignTicketDTO;
use NebulaDesk\Domain\Entities\User;
use NebulaDesk\Domain\Repositories\TicketRepositoryInterface;
use NebulaDesk\Domain\Repositories\UserRepositoryInterface;
use NebulaDesk\Application\Services\OrganizationGuard;
use Illuminate\Support\Facades\DB;

class AssignTicketUseCase
{
    private TicketRepositoryInterface $ticketRepository;
    private UserRepositoryInterface $userRepository;
    private OrganizationGuard $organizationGuard;

    public function __construct(
        TicketRepositoryInterface $ticketRepository,
        UserRepositoryInterface $userRepository,
        OrganizationGuard $organizationGuard
    ) {
        $this->ticketRepository = $ticketRepository;
        $this->userRepository = $userRepository;
        $this->organizationGuard = $organizationGuard;
    }

    public function execute(AssignTicketDTO $dto, User $currentUser): void
    {
        $ticket = $this->ticketRepository->findById($dto->ticketId);
        if (!$ticket) {
            throw new \Exception('Ticket not found');
        }

        // Validate current user can access this ticket
        $this->organizationGuard->ensureSameOrganization($ticket, $currentUser);

        $assignedUser = $this->userRepository->findById($dto->assignedTo);
        if (!$assignedUser) {
            throw new \Exception('User not found');
        }

        // Validate assigned user belongs to same organization
        $this->organizationGuard->ensureSameOrganizationBetweenUsers($currentUser, $assignedUser);

        // Update ticket assignment directly via DB
        DB::table('tickets')
            ->where('id', $dto->ticketId)
            ->update(['assignee_id' => $dto->assignedTo, 'updated_at' => now()]);
    }
}
