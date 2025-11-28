<?php

namespace NebulaDesk\Application\UseCases;

use NebulaDesk\Application\DTOs\LockUserDTO;
use NebulaDesk\Domain\Repositories\UserRepositoryInterface;
use NebulaDesk\Application\Services\AuditLogService;

class LockUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private AuditLogService $auditLogService
    ) {
    }

    /**
     * Lock a user account and create audit log
     * 
     * @throws \Exception if user not found
     */
    public function execute(LockUserDTO $dto): void
    {
        $user = $this->userRepository->findById($dto->userId);

        if (!$user) {
            throw new \Exception('User not found');
        }

        // Lock the user
        $this->userRepository->lockUser($dto->userId, $dto->adminId);

        // Create audit log
        $this->auditLogService->log(
            userId: $dto->adminId,
            action: 'user.locked',
            entityType: 'user',
            entityId: $dto->userId
        );
    }
}
