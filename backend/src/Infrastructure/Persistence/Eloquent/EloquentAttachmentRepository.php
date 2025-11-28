<?php

namespace NebulaDesk\Infrastructure\Persistence\Eloquent;

use App\Models\Attachment as EloquentAttachment;
use NebulaDesk\Domain\Entities\Attachment;
use NebulaDesk\Domain\Repositories\AttachmentRepositoryInterface;

class EloquentAttachmentRepository implements AttachmentRepositoryInterface
{
    public function save(Attachment $attachment): Attachment
    {
        $eloquentAttachment = EloquentAttachment::create([
            'ticket_id' => $attachment->ticketId,
            'user_id' => $attachment->userId,
            'filename' => $attachment->filename,
            'original_filename' => $attachment->originalFilename,
            'filepath' => $attachment->filepath,
            'mimetype' => $attachment->mimetype,
            'size' => $attachment->size,
        ]);

        return $this->toDomain($eloquentAttachment);
    }

    public function findById(int $id): ?Attachment
    {
        $eloquentAttachment = EloquentAttachment::find($id);
        return $eloquentAttachment ? $this->toDomain($eloquentAttachment) : null;
    }

    public function findByTicketId(int $ticketId): array
    {
        $eloquentAttachments = EloquentAttachment::where('ticket_id', $ticketId)
            ->orderBy('created_at', 'desc')
            ->get();

        return $eloquentAttachments->map(fn($att) => $this->toDomain($att))->toArray();
    }

    public function delete(int $id): bool
    {
        return EloquentAttachment::destroy($id) > 0;
    }

    private function toDomain(EloquentAttachment $eloquent): Attachment
    {
        return new Attachment(
            id: $eloquent->id,
            ticketId: $eloquent->ticket_id,
            userId: $eloquent->user_id,
            filename: $eloquent->filename,
            originalFilename: $eloquent->original_filename,
            filepath: $eloquent->filepath,
            mimetype: $eloquent->mimetype,
            size: $eloquent->size,
            createdAt: $eloquent->created_at
        );
    }
}
