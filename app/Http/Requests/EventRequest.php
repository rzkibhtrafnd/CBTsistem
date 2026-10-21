<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === UserRole::ADMIN;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'is_active' => ['required', 'boolean'],
            'test_ids' => ['required', 'array', 'min:1'],
            'test_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('tests', 'id')->where('is_active', true),
            ],
            'participant_ids' => ['required', 'array', 'min:1'],
            'participant_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('users', 'id')->where('role', UserRole::STUDENT->value),
            ],
        ];
    }
}
