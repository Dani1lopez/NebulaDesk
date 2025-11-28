<?php

namespace NebulaDesk\Domain\Repositories;

use NebulaDesk\Domain\Entities\Attachment;

interface AttachmentRepositoryInterface
{
    public function save(Attachment $attachment): Attachment;
    public function findById(int $id): ?Attachment;
    public function findByTicketId(int $ticketId): array;
    public function delete(int $id): bool;
}
