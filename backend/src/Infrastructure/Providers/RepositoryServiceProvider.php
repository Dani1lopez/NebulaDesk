<?php

namespace NebulaDesk\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use NebulaDesk\Domain\Repositories\UserRepositoryInterface;
use NebulaDesk\Infrastructure\Persistence\Eloquent\EloquentUserRepository;
use NebulaDesk\Domain\Repositories\AuditLogRepositoryInterface;
use NebulaDesk\Infrastructure\Persistence\Eloquent\EloquentAuditLogRepository;
use NebulaDesk\Domain\Repositories\RoleRepositoryInterface;
use NebulaDesk\Infrastructure\Persistence\Eloquent\EloquentRoleRepository;
use NebulaDesk\Domain\Repositories\PermissionRepositoryInterface;
use NebulaDesk\Infrastructure\Persistence\Eloquent\EloquentPermissionRepository;
use NebulaDesk\Domain\Repositories\AttachmentRepositoryInterface;
use NebulaDesk\Infrastructure\Persistence\Eloquent\EloquentAttachmentRepository as AttachmentRepository; // Assuming this is the correct concrete implementation

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AttachmentRepositoryInterface::class, AttachmentRepository::class);

        // Register OrganizationGuard service
        $this->app->singleton(\NebulaDesk\Application\Services\OrganizationGuard::class);

        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(\NebulaDesk\Domain\Repositories\OrganizationRepositoryInterface::class, \NebulaDesk\Infrastructure\Persistence\Eloquent\EloquentOrganizationRepository::class);
        $this->app->bind(\NebulaDesk\Domain\Repositories\TicketRepositoryInterface::class, \NebulaDesk\Infrastructure\Persistence\Eloquent\EloquentTicketRepository::class);
        $this->app->bind(\NebulaDesk\Domain\Repositories\CommentRepositoryInterface::class, \NebulaDesk\Infrastructure\Persistence\Eloquent\EloquentCommentRepository::class);
        $this->app->bind(\NebulaDesk\Domain\Repositories\AuditLogRepositoryInterface::class, \NebulaDesk\Infrastructure\Persistence\Eloquent\EloquentAuditLogRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, EloquentRoleRepository::class);
        $this->app->bind(PermissionRepositoryInterface::class, EloquentPermissionRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
