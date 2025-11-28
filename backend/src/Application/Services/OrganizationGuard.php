<?php

namespace NebulaDesk\Application\Services;

use NebulaDesk\Domain\Entities\User;
use NebulaDesk\Domain\Repositories\TicketRepositoryInterface;
use NebulaDesk\Domain\Repositories\AttachmentRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * OrganizationGuard - Centralized multi-tenant validation service
 * 
 * Ensures that users can only access resources belonging to their organization.
 * Throws AccessDeniedHttpException (403) if validation fails.
 */
class OrganizationGuard
{
    public function __construct(
        private TicketRepositoryInterface $ticketRepository,
        private AttachmentRepositoryInterface $attachmentRepository
    ) {
    }

    /**
     * Ensure that a model's organization matches the user's organization
     * 
     * @param object $model Model with organizationId property
     * @param User $user Authenticated user
     * @throws AccessDeniedHttpException if organizations don't match
     */
    public function ensureSameOrganization(object $model, User $user): void
    {
        if (!property_exists($model, 'organizationId')) {
            throw new \InvalidArgumentException('Model must have organizationId property');
        }

        if ($model->organizationId !== $user->organizationId) {
            throw new AccessDeniedHttpException(
                'Access denied: Resource belongs to a different organization'
            );
        }
    }

    /**
     * Ensure user can access a specific ticket
     * 
     * @param int $ticketId
     * @param User $user
     * @throws AccessDeniedHttpException if ticket not found or belongs to another organization
     */
    public function ensureTicketAccess(int $ticketId, User $user): void
    {
        $ticket = $this->ticketRepository->findById($ticketId);
        
        if (!$ticket) {
            throw new AccessDeniedHttpException('Ticket not found or access denied');
        }

        $this->ensureSameOrganization($ticket, $user);
    }

    /**
     * Ensure user can access a specific attachment
     * Validates both attachment and its associated ticket
     * 
     * @param int $attachmentId
     * @param User $user
     * @throws AccessDeniedHttpException if attachment/ticket not found or belongs to another organization
     */
    public function ensureAttachmentAccess(int $attachmentId, User $user): void
    {
        $attachment = $this->attachmentRepository->findById($attachmentId);
        
        if (!$attachment) {
            throw new AccessDeniedHttpException('Attachment not found or access denied');
        }

        // Also validate the ticket associated with the attachment
        $this->ensureTicketAccess($attachment->ticketId, $user);
    }

    /**
     * Ensure that two users belong to the same organization
     * Useful for operations like ticket assignment
     * 
     * @param User $user1
     * @param User $user2
     * @throws AccessDeniedHttpException if users are from different organizations
     */
    public function ensureSameOrganizationBetweenUsers(User $user1, User $user2): void
    {
        if ($user1->organizationId !== $user2->organizationId) {
            throw new AccessDeniedHttpException(
                'Cannot perform operation: Users belong to different organizations'
            );
        }
    }
}
