<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;

class CompanyPolicy
{
    public function view(User $user, Company $company): bool
    {
        return $company->hasUser($user) || $user->hasRole('Admin');
    }

    public function update(User $user, Company $company): bool
    {
        return $company->isAdmin($user) || $user->hasRole('Admin');
    }

    public function delete(User $user, Company $company): bool
    {
        return $company->isOwner($user) || $user->hasRole('Admin');
    }

    public function manageMembers(User $user, Company $company): bool
    {
        return $company->isAdmin($user) || $user->hasRole('Admin');
    }
}
