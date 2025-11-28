<?php

namespace NebulaDesk\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use NebulaDesk\Application\DTOs\NotificationDTO;
use NebulaDesk\Application\UseCases\SendNotificationUseCase;

class NotificationController extends Controller
{
    private SendNotificationUseCase $sendNotificationUseCase;

    public function __construct(SendNotificationUseCase $sendNotificationUseCase)
    {
        $this->sendNotificationUseCase = $sendNotificationUseCase;
    }

    /**
     * Send a notification (email or webhook)
     */
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:email,webhook',
            'recipient' => 'required|string',
            'subject' => 'required|string',
            'message' => 'required|string',
            'data' => 'array'
        ]);

        $dto = new NotificationDTO(
            type: $request->input('type'),
            recipient: $request->input('recipient'),
            subject: $request->input('subject'),
            message: $request->input('message'),
            data: $request->input('data', [])
        );

        $success = $this->sendNotificationUseCase->execute($dto);

        if ($success) {
            return response()->json(['message' => 'Notification sent successfully']);
        }
        return response()->json(['message' => 'Failed to send notification'], 500);
    }
}
