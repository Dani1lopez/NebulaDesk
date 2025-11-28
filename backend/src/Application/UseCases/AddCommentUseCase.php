<?php

namespace NebulaDesk\Application\UseCases;

use NebulaDesk\Application\DTOs\AddCommentDTO;
use NebulaDesk\Domain\Entities\Comment;
use NebulaDesk\Domain\Entities\User;
use NebulaDesk\Domain\Repositories\CommentRepositoryInterface;
use NebulaDesk\Domain\Repositories\TicketRepositoryInterface;
use NebulaDesk\Application\Services\OrganizationGuard;

class AddCommentUseCase
{
    public function __construct(
        private CommentRepositoryInterface $commentRepository,
        private TicketRepositoryInterface $ticketRepository,
        private OrganizationGuard $organizationGuard
    ) {
    }

    public function execute(AddCommentDTO $dto, User $user): Comment
    {
        // Verify ticket exists
        $ticket = $this->ticketRepository->findById($dto->ticketId);
        if (!$ticket) {
            throw new \Exception("Ticket not found");
        }

        // Validate multi-tenant access - user must belong to same organization as ticket
        $this->organizationGuard->ensureSameOrganization($ticket, $user);

        $comment = new Comment(
            id: null,
            content: $dto->content,
            ticketId: $dto->ticketId,
            userId: $dto->userId
        );

        return $this->commentRepository->save($comment);
    }
}
