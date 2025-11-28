<?php

namespace NebulaDesk\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use NebulaDesk\Application\DTOs\CreateTicketDTO;
use NebulaDesk\Application\UseCases\CreateTicketUseCase;
use NebulaDesk\Application\UseCases\ListTicketsUseCase;
use NebulaDesk\Application\UseCases\GetTicketUseCase;
use NebulaDesk\Application\UseCases\AssignTicketUseCase;
use NebulaDesk\Application\UseCases\UpdateTicketStatusUseCase;
use NebulaDesk\Application\DTOs\AssignTicketDTO;
use NebulaDesk\Application\DTOs\UpdateTicketStatusDTO;
use NebulaDesk\Application\UseCases\UpdateTicketUseCase;
use NebulaDesk\Application\UseCases\DeleteTicketUseCase;
use NebulaDesk\Application\DTOs\UpdateTicketDTO;
use NebulaDesk\Application\DTOs\DeleteTicketDTO;
use NebulaDesk\Domain\Entities\User as DomainUser;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TicketController extends Controller
{
    use AuthorizesRequests;
    public function __construct(
        private CreateTicketUseCase $createTicketUseCase,
        private ListTicketsUseCase $listTicketsUseCase,
        private GetTicketUseCase $getTicketUseCase,
        private AssignTicketUseCase $assignTicketUseCase,
        private UpdateTicketStatusUseCase $updateTicketStatusUseCase,
        private UpdateTicketUseCase $updateTicketUseCase,
        private DeleteTicketUseCase $deleteTicketUseCase
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

    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $domainUser = $this->toDomainUser($request->user());
            $ticket = $this->getTicketUseCase->execute($id, $domainUser);

            if (!$ticket) {
                return response()->json(['message' => 'Ticket not found'], 404);
            }

            // Authorization check (still using Policy for consistency)
            $eloquentTicket = \App\Models\Ticket::find($id);
            $this->authorize('view', $eloquentTicket);

            return response()->json([
                'ticket' => [
                    'id' => $ticket->id,
                    'subject' => $ticket->subject,
                    'description' => $ticket->description,
                    'status' => $ticket->status,
                    'priority' => $ticket->priority,
                    'created_at' => $ticket->createdAt->format('Y-m-d H:i:s'),
                ]
            ]);
        } catch (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', \App\Models\Ticket::class);

        $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high,critical',
            'organization_id' => 'required|exists:organizations,id',
        ]);

        $dto = new CreateTicketDTO(
            subject: $request->subject,
            description: $request->description,
            priority: $request->priority,
            requesterId: $request->user()->id,
            organizationId: $request->organization_id
        );

        $ticket = $this->createTicketUseCase->execute($dto);

        return response()->json([
            'message' => 'Ticket created successfully',
            'ticket' => [
                'id' => $ticket->id,
                'subject' => $ticket->subject,
                'status' => $ticket->status,
            ]
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\Ticket::class);

        $user = $request->user();

        // Get filter parameters
        $search = $request->query('search');
        $status = $request->query('status');
        $priority = $request->query('priority');
        $assignedTo = $request->query('assignee_id') ? (int) $request->query('assignee_id') : null;

        $tickets = $this->listTicketsUseCase->execute(
            $user,
            $search,
            $status,
            $priority,
            $assignedTo
        );

        $data = array_map(fn($ticket) => [
            'id' => $ticket->id,
            'subject' => $ticket->subject,
            'description' => $ticket->description,
            'status' => $ticket->status,
            'priority' => $ticket->priority,
            'created_at' => $ticket->createdAt->format('Y-m-d H:i:s'),
        ], $tickets);

        return response()->json(['tickets' => $data]);
    }

    /**
     * Assign a ticket to a user
     */
    public function assign(Request $request, int $id): JsonResponse
    {
        // Authorization check
        $eloquentTicket = \App\Models\Ticket::find($id);
        if (!$eloquentTicket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }
        $this->authorize('assign', $eloquentTicket);

        $request->validate([
            'assignee_id' => 'required|integer|exists:users,id',
        ]);

        try {
            $domainUser = $this->toDomainUser($request->user());
            $dto = new AssignTicketDTO(
                ticketId: $id,
                assignedTo: $request->input('assignee_id')
            );

            $this->assignTicketUseCase->execute($dto, $domainUser);

            return response()->json(['message' => 'Ticket assigned successfully']);
        } catch (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Update ticket status
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        // Authorization check
        $eloquentTicket = \App\Models\Ticket::find($id);
        if (!$eloquentTicket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }
        $this->authorize('updateStatus', $eloquentTicket);

        $request->validate([
            'status' => 'required|in:open,in-progress,resolved,closed',
        ]);

        try {
            $domainUser = $this->toDomainUser($request->user());
            $dto = new UpdateTicketStatusDTO(
                ticketId: $id,
                status: $request->input('status')
            );

            $this->updateTicketStatusUseCase->execute($dto, $domainUser);

            return response()->json(['message' => 'Ticket status updated successfully']);
        } catch (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Update ticket details
     */
    public function update(Request $request, int $id): JsonResponse
    {
        // Authorization check
        $eloquentTicket = \App\Models\Ticket::find($id);
        if (!$eloquentTicket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }
        $this->authorize('update', $eloquentTicket);

        $request->validate([
            'subject' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'priority' => 'sometimes|in:low,medium,high,critical',
        ]);

        try {
            $domainUser = $this->toDomainUser($request->user());

            $dto = new UpdateTicketDTO(
                ticketId: $id,
                subject: $request->input('subject'),
                description: $request->input('description'),
                priority: $request->input('priority')
            );

            $updatedTicket = $this->updateTicketUseCase->execute($dto, $domainUser);

            return response()->json([
                'message' => 'Ticket updated successfully',
                'ticket' => [
                    'id' => $updatedTicket->id,
                    'subject' => $updatedTicket->subject,
                    'description' => $updatedTicket->description,
                    'priority' => $updatedTicket->priority,
                    'status' => $updatedTicket->status,
                ]
            ]);
        } catch (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Delete a ticket
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        // Authorization check
        $eloquentTicket = \App\Models\Ticket::find($id);
        if (!$eloquentTicket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }
        $this->authorize('delete', $eloquentTicket);

        try {
            $domainUser = $this->toDomainUser($request->user());
            $dto = new DeleteTicketDTO(ticketId: $id);
            $this->deleteTicketUseCase->execute($dto, $domainUser);

            return response()->json(['message' => 'Ticket deleted successfully'], 200);
        } catch (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
