<?php

namespace NebulaDesk\Application\UseCases;

use NebulaDesk\Application\DTOs\UnlockUserDTO;
use NebulaDesk\Domain\Repositories\UserRepositoryInterface;
use NebulaDesk\Application\Services\AuditLogService;

class UnlockUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private AuditLogService $auditLogService
    ) {
    }

    /**
     * Unlock a user account and create audit log
     * 
     * @throws \Exception if user not found
     */
    public function execute(UnlockUserDTO $dto): void
    {
        $user = $this->userRepository->findById($dto->userId);

        if (!$user) {
            throw new \Exception('User not found');
        }

        // Unlock the user
        $this->userRepository->unlockUser($dto->userId);

        // Create audit log
        $this->auditLogService->log(
            userId: $dto->adminId,
            action: 'user.unlocked',
            entityType: 'user',
            entityId: $dto->userId
        );
    }
}
