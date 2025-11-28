<?php

namespace NebulaDesk\Domain\Repositories;

use NebulaDesk\Domain\Entities\Comment;

interface CommentRepositoryInterface
{
    public function save(Comment $comment): Comment;
    public function findByTicketId(int $ticketId): array;
}
