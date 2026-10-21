<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Models\Choice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === UserRole::ADMIN;
    }

    public function rules(): array
    {
        $exam = $this->route('exam') ?? $this->route('test');
        $examId = is_object($exam) ? $exam->id : $exam;

        return [
            'title' => ['required', 'string', 'max:255'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'is_active' => ['required', 'boolean'],
            'description' => ['nullable', 'string', 'max:10000'],
            'questions' => ['required', 'array', 'min:1', 'max:100'],

            'questions.*.id' => [
                'nullable',
                'integer',
                Rule::exists('questions', 'id')->where('test_id', $examId ?? 0),
            ],
            'questions.*.question_text' => ['required', 'string', 'max:10000'],
            'questions.*.score_weight' => ['required', 'integer', 'min:1', 'max:100000'],
            'questions.*.choices' => ['required', 'array', 'min:2', 'max:8'],

            'questions.*.choices.*.id' => ['nullable', 'integer', 'exists:choices,id'],
            'questions.*.choices.*.choice_text' => ['required', 'string', 'max:5000'],
            'questions.*.choices.*.is_correct' => ['required', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            $questions = $this->input('questions', []);
            if (! is_array($questions) || empty($questions)) {
                return;
            }

            $exam = $this->route('exam') ?? $this->route('test');
            $examId = is_object($exam) ? $exam->id : $exam;

            $validChoicesByQuestion = [];
            if ($examId) {
                $validChoicesByQuestion = Choice::whereHas('question', fn ($q) => $q->where('test_id', $examId))
                    ->get()
                    ->groupBy('question_id')
                    ->map(fn ($choices) => $choices->pluck('id')->toArray())
                    ->toArray();
            }

            foreach ($questions as $qIndex => $question) {
                if (! is_array($question)) {
                    continue;
                }

                $questionId = $question['id'] ?? null;
                $validChoiceIds = $questionId ? ($validChoicesByQuestion[$questionId] ?? []) : [];
                $choices = $question['choices'] ?? [];

                $correctCount = 0;

                foreach (is_array($choices) ? $choices : [] as $cIndex => $choice) {
                    if (! empty($choice['id']) && ! in_array((int) $choice['id'], $validChoiceIds, true)) {
                        $validator->errors()->add("questions.{$qIndex}.choices.{$cIndex}.id", 'Pilihan jawaban tidak sesuai dengan soal ini.');
                    }

                    if (filter_var($choice['is_correct'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                        $correctCount++;
                    }
                }

                if ($correctCount !== 1) {
                    $validator->errors()->add("questions.{$qIndex}.choices", 'Tentukan tepat satu pilihan sebagai jawaban benar.');
                }
            }
        }];
    }
}
