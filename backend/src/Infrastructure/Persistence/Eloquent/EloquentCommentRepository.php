<?php

namespace NebulaDesk\Infrastructure\Persistence\Eloquent;

use App\Models\Comment as EloquentComment;
use NebulaDesk\Domain\Entities\Comment;
use NebulaDesk\Domain\Repositories\CommentRepositoryInterface;

class EloquentCommentRepository implements CommentRepositoryInterface
{
    public function save(Comment $comment): Comment
    {
        $eloquentComment = EloquentComment::updateOrCreate(
            ['id' => $comment->id],
            [
                'content' => $comment->content,
                'ticket_id' => $comment->ticketId,
                'user_id' => $comment->userId,
            ]
        );

        return $this->toDomain($eloquentComment);
    }

    public function findByTicketId(int $ticketId): array
    {
        $eloquentComments = EloquentComment::where('ticket_id', $ticketId)
            ->with('user') // Eager load user for display
            ->orderBy('created_at', 'asc')
            ->get();

        return $eloquentComments->map(fn($comment) => $this->toDomain($comment))->toArray();
    }

    private function toDomain(EloquentComment $eloquentComment): Comment
    {
        return new Comment(
            id: $eloquentComment->id,
            content: $eloquentComment->content,
            ticketId: $eloquentComment->ticket_id,
            userId: $eloquentComment->user_id,
            createdAt: \DateTimeImmutable::createFromMutable($eloquentComment->created_at),
            updatedAt: \DateTimeImmutable::createFromMutable($eloquentComment->updated_at),
        );
    }
}
