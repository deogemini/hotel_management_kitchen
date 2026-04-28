<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'role_id',
        'avatar',
        'password',
        'failed_login_attempts',
        'last_failed_login_at',
        'account_locked_until',
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
            'password' => 'hashed',
            'last_failed_login_at' => 'datetime',
            'account_locked_until' => 'datetime',
        ];
    }

    public function roleRecord(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function effectiveRoleName(): ?string
    {
        $role = $this->roleRecord?->name ?? $this->role;

        return [
            'admin' => 'hotel_manager',
            'user' => 'cashier',
        ][$role] ?? $role;
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->effectiveRoleName(), $roles, true);
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->roleRecord) {
            return $this->roleRecord()
                ->whereHas('permissions', fn ($query) => $query->where('name', $permission))
                ->exists();
        }

        $fallback = [
            'hotel_manager' => ['*'],
            'cashier' => [
                'dashboard.view',
                'rooms.manage',
                'guests.manage',
                'bookings.manage',
                'checkin.manage',
                'restaurant_orders.manage',
                'payments.manage',
            ],
            'chef' => [
                'kitchen_orders.view',
                'kitchen_orders.update_status',
            ],
        ];

        $permissions = $fallback[$this->effectiveRoleName()] ?? [];

        return in_array('*', $permissions, true) || in_array($permission, $permissions, true);
    }
}
