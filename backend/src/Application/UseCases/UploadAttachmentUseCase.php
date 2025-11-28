<?php

namespace NebulaDesk\Application\UseCases;

use NebulaDesk\Application\DTOs\UploadAttachmentDTO;
use NebulaDesk\Domain\Entities\Attachment;
use NebulaDesk\Domain\Entities\User;
use NebulaDesk\Domain\Repositories\AttachmentRepositoryInterface;
use NebulaDesk\Domain\Repositories\TicketRepositoryInterface;
use NebulaDesk\Application\Services\OrganizationGuard;
use Illuminate\Support\Str;

class UploadAttachmentUseCase
{
    public function __construct(
        private AttachmentRepositoryInterface $attachmentRepository,
        private TicketRepositoryInterface $ticketRepository,
        private OrganizationGuard $organizationGuard
    ) {
    }

    public function execute(UploadAttachmentDTO $dto, User $user): Attachment
    {
        // Verify ticket exists
        $ticket = $this->ticketRepository->findById($dto->ticketId);
        if (!$ticket) {
            throw new \Exception('Ticket not found');
        }

        // Validate multi-tenant access
        $this->organizationGuard->ensureSameOrganization($ticket, $user);

        $file = $dto->file;

        // Generate unique filename
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

        // Store file in storage/app/attachments/{organizationId} for isolation
        $filepath = $file->storeAs("attachments/{$user->organizationId}", $filename);

        // Create attachment entity (without ID since it's not persisted yet)
        $attachment = new Attachment(
            id: 0, // Temporary, will be set by repository
            ticketId: $dto->ticketId,
            userId: $dto->userId,
            filename: $filename,
            originalFilename: $file->getClientOriginalName(),
            filepath: $filepath,
            mimetype: $file->getMimeType(),
            size: $file->getSize(),
            createdAt: new \DateTime()
        );

        return $this->attachmentRepository->save($attachment);
    }
}
