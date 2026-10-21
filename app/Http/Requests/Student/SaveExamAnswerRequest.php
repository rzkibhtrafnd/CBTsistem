<?php

namespace App\Http\Requests\Student;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class SaveExamAnswerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === UserRole::STUDENT;
    }

    public function rules(): array
    {
        return [
            'choice_id' => ['present', 'nullable', 'integer', 'exists:choices,id'],
            'is_doubtful' => ['required', 'boolean'],
        ];
    }
}
