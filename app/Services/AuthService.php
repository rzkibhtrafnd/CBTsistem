<?php

namespace App\Services;

use App\Models\User;

class AuthService
{
    public function updatePassword(User $user, string $password): void
    {
        $user->update(['password' => $password]);
    }
}
