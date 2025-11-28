<?php

namespace NebulaDesk\Application\UseCases;

use NebulaDesk\Application\DTOs\NotificationDTO;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;

class SendNotificationUseCase
{
    public function execute(NotificationDTO $dto): bool
    {
        try {
            if ($dto->type === 'email') {
                return $this->sendEmail($dto);
            } elseif ($dto->type === 'webhook') {
                return $this->sendWebhook($dto);
            }
            throw new \Exception('Invalid notification type');
        } catch (\Exception $e) {
            \Log::error('Notification failed: ' . $e->getMessage());
            return false;
        }
    }

    private function sendEmail(NotificationDTO $dto): bool
    {
        // Using Laravel Mail - in production, configure MAIL_* in .env
        Mail::raw($dto->message, function ($message) use ($dto) {
            $message->to($dto->recipient)
                ->subject($dto->subject);
        });
        return true;
    }

    private function sendWebhook(NotificationDTO $dto): bool
    {
        $response = Http::post($dto->recipient, [
            'subject' => $dto->subject,
            'message' => $dto->message,
            'data' => $dto->data,
        ]);
        return $response->successful();
    }
}
