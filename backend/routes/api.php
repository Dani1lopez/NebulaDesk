<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use NebulaDesk\Infrastructure\Http\Controllers\AuthController;
use NebulaDesk\Infrastructure\Http\Controllers\PasswordResetController;
use NebulaDesk\Infrastructure\Http\Controllers\EmailVerificationController;

Route::post('/password/forgot', [PasswordResetController::class, 'forgotPassword'])
    ->middleware('throttle:password_reset');
Route::post('/password/reset', [PasswordResetController::class, 'resetPassword'])
    ->middleware('throttle:password_reset');

Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware('signed')
    ->name('verification.verify');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        $user = $request->user();
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'organization_id' => $user->organization_id,
            'role' => $user->role,
            'email_verified' => $user->hasVerifiedEmail(),
        ]);
    });

    // Email Verification Resend (Must be accessible without verified middleware)
    Route::post('/email/resend', [EmailVerificationController::class, 'sendVerificationEmail'])
        ->middleware('throttle:6,1');

    // Protected routes requiring email verification
    Route::middleware('verified')->group(function () {
        // Organization routes
        Route::get('/organizations', [\NebulaDesk\Infrastructure\Http\Controllers\OrganizationController::class, 'index']);
        
        // Organization Users (Must be before /organizations/{id})
        Route::post('/organizations/users', [\NebulaDesk\Infrastructure\Http\Controllers\OrganizationController::class, 'inviteUser']);
        Route::get('/organizations/users', [\NebulaDesk\Infrastructure\Http\Controllers\OrganizationController::class, 'listUsers']);

        Route::get('/organizations/{id}', [\NebulaDesk\Infrastructure\Http\Controllers\OrganizationController::class, 'show']);

        // Admin only organization management
        Route::middleware('role:admin')->group(function () {
            Route::post('/organizations', [\NebulaDesk\Infrastructure\Http\Controllers\OrganizationController::class, 'store']);
            Route::put('/organizations/{id}', [\NebulaDesk\Infrastructure\Http\Controllers\OrganizationController::class, 'update']);
            Route::delete('/organizations/{id}', [\NebulaDesk\Infrastructure\Http\Controllers\OrganizationController::class, 'destroy']);
        });

        // Ticket routes with organization check
        Route::middleware('check.organization')->group(function () {
            Route::post('/tickets', [\NebulaDesk\Infrastructure\Http\Controllers\TicketController::class, 'store']);
            Route::get('/tickets', [\NebulaDesk\Infrastructure\Http\Controllers\TicketController::class, 'index']);
            Route::get('/tickets/{id}', [\NebulaDesk\Infrastructure\Http\Controllers\TicketController::class, 'show']);
            Route::put('/tickets/{id}', [\NebulaDesk\Infrastructure\Http\Controllers\TicketController::class, 'update']);
            Route::delete('/tickets/{id}', [\NebulaDesk\Infrastructure\Http\Controllers\TicketController::class, 'destroy']);
            Route::put('/tickets/{id}/assign', [\NebulaDesk\Infrastructure\Http\Controllers\TicketController::class, 'assign']);
            Route::put('/tickets/{id}/status', [\NebulaDesk\Infrastructure\Http\Controllers\TicketController::class, 'updateStatus']);

            Route::get('/dashboard/metrics', [\NebulaDesk\Infrastructure\Http\Controllers\DashboardController::class, 'index']);
        });

        Route::post('/tickets/{id}/comments', [\NebulaDesk\Infrastructure\Http\Controllers\CommentController::class, 'store']);
        Route::get('/tickets/{id}/comments', [\NebulaDesk\Infrastructure\Http\Controllers\CommentController::class, 'index']);
        Route::get('/audit-logs', [NebulaDesk\Infrastructure\Http\Controllers\AuditLogController::class, 'index']);

        // Attachments
        Route::post('/tickets/{id}/attachments', [\NebulaDesk\Infrastructure\Http\Controllers\AttachmentController::class, 'upload']);
        Route::get('/tickets/{id}/attachments', [\NebulaDesk\Infrastructure\Http\Controllers\AttachmentController::class, 'index']);
        Route::get('/attachments/{id}/download', [\NebulaDesk\Infrastructure\Http\Controllers\AttachmentController::class, 'download']);

        // SLA Management
        Route::post('/tickets/{id}/sla', [\NebulaDesk\Infrastructure\Http\Controllers\SlaController::class, 'setSla']);
        Route::get('/tickets/{id}/sla', [\NebulaDesk\Infrastructure\Http\Controllers\SlaController::class, 'getSla']);

        // Notifications
        Route::post('/notifications', [\NebulaDesk\Infrastructure\Http\Controllers\NotificationController::class, 'send']);

        // Roles & Permissions - Admin Only
        Route::middleware('role:admin')->group(function () {
            Route::get('/roles', [\NebulaDesk\Infrastructure\Http\Controllers\RoleController::class, 'index']);
            Route::post('/roles', [\NebulaDesk\Infrastructure\Http\Controllers\RoleController::class, 'store']);
            Route::post('/users/{id}/roles', [\NebulaDesk\Infrastructure\Http\Controllers\RoleController::class, 'assignToUser']);

            // User management routes (Admin/Owner only)
            Route::get('/users', [\NebulaDesk\Infrastructure\Http\Controllers\UserController::class, 'index']); // List all users
            Route::get('/users/{id}', [\NebulaDesk\Infrastructure\Http\Controllers\UserController::class, 'show']); // View user details
            Route::put('/users/{id}', [\NebulaDesk\Infrastructure\Http\Controllers\UserController::class, 'update']); // Update user
            Route::delete('/users/{id}', [\NebulaDesk\Infrastructure\Http\Controllers\UserController::class, 'destroy']); // Delete user
            Route::post('/users/{id}/lock', [\NebulaDesk\Infrastructure\Http\Controllers\UserController::class, 'lock']); // Lock user
            Route::post('/users/{id}/unlock', [\NebulaDesk\Infrastructure\Http\Controllers\UserController::class, 'unlock']); // Unlock user
        });

        // User profile (self-service, any authenticated user)
        Route::put('/user/profile', [\NebulaDesk\Infrastructure\Http\Controllers\UserController::class, 'updateProfile']);

        Route::get('/sla/dashboard', [\NebulaDesk\Infrastructure\Http\Controllers\SlaController::class, 'dashboard']);
    });
});
