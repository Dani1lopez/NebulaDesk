<?php

namespace NebulaDesk\Application\UseCases;

use Illuminate\Support\Facades\Hash;
use NebulaDesk\Application\DTOs\LoginUserDTO;
use NebulaDesk\Domain\Entities\User;
use NebulaDesk\Domain\Repositories\UserRepositoryInterface;

class LoginUserUseCase
{
    // Maximum failed login attempts before auto-lock
    private const MAX_FAILED_ATTEMPTS = 5;

    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    /**
     * Execute login attempt
     * 
     * @return array{status: 'success'|'locked'|'invalid', user: ?User}
     */
    public function execute(LoginUserDTO $dto): array
    {
        $user = $this->userRepository->findByEmail($dto->email);

        // If user doesn't exist, return generic invalid credentials
        // (don't reveal whether email exists)
        if (!$user) {
            return ['status' => 'invalid', 'user' => null];
        }

        // Check if account is locked BEFORE password verification
        if ($user->isLocked) {
            return ['status' => 'locked', 'user' => null];
        }

        // Verify password
        if (!Hash::check($dto->password, $user->password)) {
            // Password is incorrect - increment failed attempts
            $this->userRepository->incrementFailedAttempts($user->id);
            
            // Reload user to get updated failed attempts count
            $user = $this->userRepository->findById($user->id);
            
            // Check if threshold reached
            if ($user->failedLoginAttempts >= self::MAX_FAILED_ATTEMPTS) {
                // Auto-lock the account
                $this->userRepository->autoLockUser($user->id);
                return ['status' => 'locked', 'user' => null];
            }
            
            return ['status' => 'invalid', 'user' => null];
        }

        // Password is correct - reset failed attempts and unlock
        $this->userRepository->resetFailedAttempts($user->id);
        
        // Reload user to get fresh state
        $user = $this->userRepository->findById($user->id);

        return ['status' => 'success', 'user' => $user];
    }
}
