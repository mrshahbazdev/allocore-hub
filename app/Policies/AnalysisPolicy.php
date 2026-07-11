<?php

namespace App\Policies;

use App\Models\Analysis;
use App\Models\User;

class AnalysisPolicy
{
    public function view(User $user, Analysis $analysis): bool
    {
        return $analysis->company?->hasUser($user) || $user->hasRole('Admin');
    }

    public function update(User $user, Analysis $analysis): bool
    {
        return $analysis->company?->isAdmin($user) || $user->hasRole('Admin');
    }

    public function delete(User $user, Analysis $analysis): bool
    {
        return $analysis->company?->isAdmin($user) || $user->hasRole('Admin');
    }
}
