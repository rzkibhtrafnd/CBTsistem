<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        $role = $request->user()->role;

        return match ($role) {
            UserRole::ADMIN => redirect()->route('admin.dashboard'),
            UserRole::STUDENT => redirect()->route('student.dashboard'),
        };
    }
}
