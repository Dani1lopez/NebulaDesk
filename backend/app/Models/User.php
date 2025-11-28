<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
use HasFactory, Notifiable, \Laravel\Sanctum\HasApiTokens;
    use \Illuminate\Auth\Passwords\CanResetPassword;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'organization_id',
        'role',
        'avatar',
        'is_locked',
        'locked_at',
        'locked_by',
        'failed_login_attempts',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'locked_at' => 'datetime',
            'is_locked' => 'boolean',
        ];
    }

    /**
     * Check if the user account is locked
     */
    public function isLocked(): bool
    {
        return $this->is_locked === true;
    }

    /**
     * Lock the user account
     */
    public function lock(int $adminId): void
    {
        $this->is_locked = true;
        $this->locked_at = now();
        $this->locked_by = $adminId;
        $this->save();
    }

    /**
     * Unlock the user account
     */
    public function unlock(): void
    {
        $this->is_locked = false;
        $this->locked_at = null;
        $this->locked_by = null;
        $this->failed_login_attempts = 0;
        $this->save();
    }

    /**
     * Check if user has admin role
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'owner']);
    }

    /**
     * Check if user has owner role
     */
    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    /**
     * Check if user can manage other users
     */
    public function canManageUsers(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\VerifyEmailNotification);
    }
}
