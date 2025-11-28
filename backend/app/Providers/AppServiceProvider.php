<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use NebulaDesk\Domain\Repositories\UserRepositoryInterface;
use NebulaDesk\Infrastructure\Persistence\Eloquent\EloquentUserRepository;
use NebulaDesk\Domain\Repositories\OrganizationRepositoryInterface;
use NebulaDesk\Infrastructure\Persistence\Eloquent\EloquentOrganizationRepository;
use NebulaDesk\Domain\Repositories\TicketRepositoryInterface;
use NebulaDesk\Infrastructure\Persistence\Eloquent\EloquentTicketRepository;
use NebulaDesk\Domain\Repositories\CommentRepositoryInterface;
use NebulaDesk\Infrastructure\Persistence\Eloquent\EloquentCommentRepository;
use NebulaDesk\Domain\Repositories\AuditLogRepositoryInterface;
use NebulaDesk\Infrastructure\Persistence\Eloquent\EloquentAuditLogRepository;
use NebulaDesk\Domain\Repositories\RoleRepositoryInterface;
use NebulaDesk\Infrastructure\Persistence\Eloquent\EloquentRoleRepository;
use NebulaDesk\Domain\Repositories\PermissionRepositoryInterface;
use NebulaDesk\Infrastructure\Persistence\Eloquent\EloquentPermissionRepository;
use NebulaDesk\Domain\Repositories\AttachmentRepositoryInterface;
use NebulaDesk\Infrastructure\Persistence\Eloquent\EloquentAttachmentRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(OrganizationRepositoryInterface::class, EloquentOrganizationRepository::class);
        $this->app->bind(TicketRepositoryInterface::class, EloquentTicketRepository::class);
        $this->app->bind(CommentRepositoryInterface::class, EloquentCommentRepository::class);
        $this->app->bind(AuditLogRepositoryInterface::class, EloquentAuditLogRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, EloquentRoleRepository::class);
        $this->app->bind(PermissionRepositoryInterface::class, EloquentPermissionRepository::class);
        $this->app->bind(AttachmentRepositoryInterface::class, EloquentAttachmentRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Organization::class, \App\Policies\OrganizationPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Ticket::class, \App\Policies\TicketPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\User::class, \App\Policies\UserPolicy::class);

        // Rate Limiting Configuration
        \Illuminate\Support\Facades\RateLimiter::for('api', function (\Illuminate\Http\Request $request) {
            return $request->user()
                ? \Illuminate\Cache\RateLimiting\Limit::perMinute(60)->by($request->user()->id)
                : \Illuminate\Cache\RateLimiting\Limit::perMinute(60)->by($request->ip());
        });

        \Illuminate\Support\Facades\RateLimiter::for('login', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(10)->by($request->ip());
        });

        \Illuminate\Support\Facades\RateLimiter::for('password_reset', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(3)->by($request->ip());
        });
    }
}
