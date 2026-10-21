<?php

namespace App\Http\Requests\Admin;

use App\Enums\ExamSessionStatus;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === UserRole::ADMIN;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::enum(ExamSessionStatus::class)],
            'per_page' => ['nullable', Rule::in([10, 25, 50])],
        ];
    }
}
