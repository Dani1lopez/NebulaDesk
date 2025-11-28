<?php

namespace NebulaDesk\Application\DTOs;

class AddCommentDTO
{
    public function __construct(
        public string $content,
        public int $ticketId,
        public int $userId,
    ) {
    }
}
