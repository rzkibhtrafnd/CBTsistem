<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function getFilteredUsers(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $perPage = in_array($perPage, [10, 25, 50], true) ? $perPage : 10;

        return User::query()
            ->withCount(['events', 'examSessions'])
            ->filter($filters)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getUserDetails(User $user): User
    {
        $user->loadCount(['events', 'examSessions']);

        if ($user->role === UserRole::STUDENT) {
            $user->load(['events' => fn ($query) => $query->withCount('tests')->latest('start_time')]);
        }

        return $user;
    }

    public function createUser(array $data): User
    {
        return User::create($data);
    }

    public function updateUser(User $user, array $data): User
    {
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->update($data);

        return $user;
    }

    public function deleteUser(User $user, User $actor): void
    {
        if ($user->is($actor)) {
            throw ValidationException::withMessages([
                'user' => 'Akun yang sedang digunakan tidak dapat dihapus.',
            ]);
        }

        if ($user->examSessions()->exists() || ($user->role === UserRole::STUDENT && $user->events()->exists())) {
            throw ValidationException::withMessages([
                'user' => 'User yang sudah memiliki riwayat ujian atau penugasan event tidak dapat dihapus.',
            ]);
        }

        $user->delete();
    }
}
