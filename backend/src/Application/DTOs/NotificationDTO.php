<?php

namespace NebulaDesk\Application\DTOs;

class NotificationDTO
{
    public function __construct(
        public readonly string $type, // 'email' or 'webhook'
        public readonly string $recipient, // email address or webhook URL
        public readonly string $subject,
        public readonly string $message,
        public readonly array $data = []
    ) {
    }
}
