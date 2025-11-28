<?php

namespace NebulaDesk\Application\DTOs;

use Illuminate\Http\UploadedFile;

readonly class UploadAttachmentDTO
{
    public function __construct(
        public int $ticketId,
        public int $userId,
        public UploadedFile $file
    ) {
    }
}
