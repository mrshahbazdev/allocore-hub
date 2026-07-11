<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;

    public const ROLE_OWNER = 'owner';

    public const ROLE_ADMIN = 'admin';

    public const ROLE_MEMBER = 'member';

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'industry',
        'currency',
        'country',
        'description',
        'status',
        'trial_ends_at',
        'billing_email',
        'vat_id',
        'billing_address',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'company_user')
            ->withPivot('role', 'is_default', 'invited_at', 'accepted_at')
            ->withTimestamps();
    }

    public function analyses(): HasMany
    {
        return $this->hasMany(Analysis::class);
    }

    public function tools(): BelongsToMany
    {
        return $this->belongsToMany(Tool::class, 'company_tool')
            ->withPivot('status', 'expires_at')
            ->withTimestamps();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription(): ?Subscription
    {
        return $this->subscriptions()
            ->whereIn('status', [Subscription::STATUS_ACTIVE, Subscription::STATUS_TRIALING])
            ->where(function ($query) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>', now());
            })
            ->latest()
            ->first();
    }

    public function hasToolAccess(Tool|string $tool): bool
    {
        $toolSlug = $tool instanceof Tool ? $tool->slug : $tool;

        $companyTool = $this->tools()->where('tools.slug', $toolSlug)->first();

        if (! $companyTool) {
            return false;
        }

        if ($companyTool->pivot->expires_at && $companyTool->pivot->expires_at->isPast()) {
            return false;
        }

        return in_array($companyTool->pivot->status, ['active', 'trialing'], true);
    }

    public function hasUser(User $user): bool
    {
        return $this->users()->where('users.id', $user->id)->exists();
    }

    public function userRole(User $user): ?string
    {
        return $this->users()->where('users.id', $user->id)->first()?->pivot?->role;
    }

    public function isOwner(User $user): bool
    {
        return $this->userRole($user) === self::ROLE_OWNER;
    }

    public function isAdmin(User $user): bool
    {
        return in_array($this->userRole($user), [self::ROLE_OWNER, self::ROLE_ADMIN], true);
    }
}
