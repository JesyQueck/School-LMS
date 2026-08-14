<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function view(User $user, User $model): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $user->is($model);
    }

    public function update(User $user, User $model): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $user->is($model);
    }
}
