<?php

namespace App\Http\Requests\Student;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class FinishExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === UserRole::STUDENT;
    }

    public function rules(): array
    {
        return [];
    }
}
