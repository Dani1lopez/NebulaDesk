<?php

namespace NebulaDesk\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use NebulaDesk\Application\DTOs\AddCommentDTO;
use NebulaDesk\Application\UseCases\AddCommentUseCase;
use NebulaDesk\Application\UseCases\ListCommentsUseCase;
use NebulaDesk\Domain\Entities\User as DomainUser;

class CommentController extends Controller
{
    public function __construct(
        private AddCommentUseCase $addCommentUseCase,
        private ListCommentsUseCase $listCommentsUseCase
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
     * Add a comment to a ticket
     */
    public function store(Request $request, int $ticketId): JsonResponse
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        try {
            $domainUser = $this->toDomainUser($request->user());
            $dto = new AddCommentDTO(
                ticketId: $ticketId,
                userId: $request->user()->id,
                content: $request->input('content')
            );

            $comment = $this->addCommentUseCase->execute($dto, $domainUser);

            return response()->json([
                'message' => 'Comment added successfully',
                'comment' => [
                    'id' => $comment->id,
                    'content' => $comment->content,
                ]
            ], 201);
        } catch (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * List all comments for a ticket
     */
    public function index(Request $request, int $ticketId): JsonResponse
    {
        try {
            $domainUser = $this->toDomainUser($request->user());
            $comments = $this->listCommentsUseCase->execute($ticketId, $domainUser);

            $data = array_map(fn($comment) => [
                'id' => $comment->id,
                'content' => $comment->content,
                'user_id' => $comment->userId,
                'ticket_id' => $comment->ticketId,
                'created_at' => $comment->createdAt->format('Y-m-d H:i:s'),
            ], $comments);

            return response()->json(['comments' => $data]);
        } catch (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
