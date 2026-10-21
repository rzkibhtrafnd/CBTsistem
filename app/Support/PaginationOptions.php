<?php

namespace App\Support;

use Illuminate\Http\Request;

class PaginationOptions
{
    public static function perPage(Request $request, int $default = 10): int
    {
        $perPage = (int) $request->query('per_page', $default);

        return in_array($perPage, [10, 25, 50], true) ? $perPage : $default;
    }
}
