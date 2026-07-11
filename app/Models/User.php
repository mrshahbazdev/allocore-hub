<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function ownedCompanies(): HasMany
    {
        return $this->hasMany(Company::class, 'user_id');
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_user')
            ->withPivot('role', 'is_default', 'invited_at', 'accepted_at')
            ->withTimestamps();
    }

    public function currentCompany(): ?Company
    {
        $companyId = session('current_company');

        if ($companyId) {
            $company = $this->companies()->where('companies.id', $companyId)->first();
            if ($company) {
                return $company;
            }
        }

        $company = $this->companies()->wherePivot('is_default', true)->first()
            ?? $this->companies()->first();

        if ($company) {
            session(['current_company' => $company->id]);
        }

        return $company;
    }

    public function setCurrentCompany(?Company $company): void
    {
        if ($company && $this->companies()->where('companies.id', $company->id)->exists()) {
            session(['current_company' => $company->id]);
        }
    }

    public function companyRole(?Company $company = null): ?string
    {
        $company ??= $this->currentCompany();

        if (! $company) {
            return null;
        }

        return $company->userRole($this);
    }

    public function isCompanyAdmin(?Company $company = null): bool
    {
        $company ??= $this->currentCompany();

        return $company?->isAdmin($this) ?? false;
    }

    public function analyses(): HasMany
    {
        return $this->hasMany(Analysis::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function paypalTransactions(): HasMany
    {
        return $this->hasMany(PaypalTransaction::class);
    }
}
