<?php

namespace NebulaDesk\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use NebulaDesk\Application\DTOs\UploadAttachmentDTO;
use NebulaDesk\Application\UseCases\UploadAttachmentUseCase;
use NebulaDesk\Application\UseCases\ListAttachmentsUseCase;
use NebulaDesk\Domain\Repositories\AttachmentRepositoryInterface;
use NebulaDesk\Domain\Repositories\TicketRepositoryInterface;
use NebulaDesk\Domain\Entities\User as DomainUser;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    public function __construct(
        private UploadAttachmentUseCase $uploadAttachmentUseCase,
        private ListAttachmentsUseCase $listAttachmentsUseCase,
        private AttachmentRepositoryInterface $attachmentRepository,
        private TicketRepositoryInterface $ticketRepository
    ) {
    }

    /**
     * Convert Eloquent User to Domain User entity
     */
    private function toDomainUser(\App\Models\User $eloquentUser): DomainUser
    {
        return new DomainUser(
            id: $eloquentUser->id,
            name: $eloquentUser->name,
            email: $eloquentUser->email,
            password: $eloquentUser->password,
            organizationId: $eloquentUser->organization_id,
            role: $eloquentUser->role,
            avatar: $eloquentUser->avatar,
            isLocked: $eloquentUser->is_locked ?? false,
            lockedAt: $eloquentUser->locked_at ? \DateTimeImmutable::createFromMutable($eloquentUser->locked_at) : null,
            lockedBy: $eloquentUser->locked_by,
            failedLoginAttempts: $eloquentUser->failed_login_attempts ?? 0
        );
    }

    /**
     * Upload an attachment to a ticket
     */
    /**
     * Upload an attachment to a ticket
     */
    public function upload(Request $request, int $ticketId): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,txt,zip', // Max 10MB + allowed types
        ]);

        try {
            $domainUser = $this->toDomainUser($request->user());
            $dto = new UploadAttachmentDTO(
                ticketId: $ticketId,
                userId: $request->user()->id,
                file: $request->file('file')
            );

            $attachment = $this->uploadAttachmentUseCase->execute($dto, $domainUser);

            return response()->json([
                'message' => 'File uploaded successfully',
                'attachment' => [
                    'id' => $attachment->id,
                    'filename' => $attachment->originalFilename,
                    'size' => $attachment->size,
                    'mimetype' => $attachment->mimetype,
                    'created_at' => $attachment->createdAt-> format('Y-m-d H:i:s'),
                ]
            ], 201);
        } catch (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * List all attachments for a ticket
     */
    public function index(Request $request, int $ticketId): JsonResponse
    {
        try {
            $domainUser = $this->toDomainUser($request->user());
            $attachments = $this->listAttachmentsUseCase->execute($ticketId, $domainUser);

            $data = array_map(fn($attachment) => [
                'id' => $attachment->id,
                'filename' => $attachment->originalFilename,
                'size' => $attachment->size,
                'mimetype' => $attachment->mimetype,
                'created_at' => $attachment->createdAt->format('Y-m-d H:i:s'),
            ], $attachments);

            return response()->json(['attachments' => $data]);
        } catch (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Download an attachment
     * CRITICAL: Multi-tenant security check - validates ticket ownership
     */
    public function download(Request $request, int $id): StreamedResponse|JsonResponse
    {
        $attachment = $this->attachmentRepository->findById($id);

        if (!$attachment) {
            return response()->json(['message' => 'Attachment not found'], 404);
        }

        // SECURITY: Validate that the attachment's ticket belongs to the user's organization
        $ticket = $this->ticketRepository->findById($attachment->ticketId);
        if (!$ticket) {
            return response()->json(['message' => 'Associated ticket not found'], 404);
        }

        if ($ticket->organizationId !== $request->user()->organization_id) {
            return response()->json(['message' => 'Access denied: Attachment belongs to a different organization'], 403);
        }

        if (!Storage::exists($attachment->filepath)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        return Storage::download($attachment->filepath, $attachment->originalFilename);
    }
}
