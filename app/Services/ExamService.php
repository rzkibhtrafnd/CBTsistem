<?php

namespace App\Services;

use App\Models\Choice;
use App\Models\Question;
use App\Models\Test;
use App\Models\UserAnswer;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExamService
{
    public function getFilteredTests(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $perPage = in_array($perPage, [10, 25, 50], true) ? $perPage : 10;

        return Test::query()
            ->withCount(['questions', 'sessions', 'events'])
            ->filter($filters)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function createTest(array $data): Test
    {
        return DB::transaction(function () use ($data) {
            $questionsData = Arr::pull($data, 'questions', []);
            $test = Test::create($data);

            foreach ($questionsData as $qData) {
                $choicesData = Arr::pull($qData, 'choices', []);
                $question = $test->questions()->create($qData);
                
                if (!empty($choicesData)) {
                    $question->choices()->createMany($choicesData);
                }
            }

            return $test;
        });
    }

    public function getTestDetails(Test $test): Test
    {
        return $test->load([
            'questions' => fn ($q) => $q->orderBy('id'),
            'questions.choices' => fn ($c) => $c->withCount('answers')->orderBy('id'),
        ])->loadCount(['questions', 'sessions', 'events']);
    }

    public function getTestForEditing(Test $test): Test
    {
        return $test->load([
            'questions' => fn ($q) => $q->orderBy('id'),
            'questions.choices' => fn ($c) => $c->orderBy('id'),
        ]);
    }

    public function updateTest(Test $test, array $data): bool
    {
        return DB::transaction(function () use ($test, $data) {
            $questionsData = collect(Arr::pull($data, 'questions', []));

            $lockedTest = Test::query()->whereKey($test->id)->lockForUpdate()->firstOrFail();
            $lockedTest->update($data);

            $existingQuestions = $lockedTest->questions()->with('choices')->get();

            if ($lockedTest->sessions()->exists() && $this->hasQuestionChanges($existingQuestions, $questionsData)) {
                throw ValidationException::withMessages([
                    'questions' => 'Bank soal yang sudah mulai dikerjakan peserta tidak dapat diubah.',
                ]);
            }

            $submittedQuestionIds = $questionsData->pluck('id')->filter()->map(fn ($id) => (int) $id)->toArray();
            $questionsToDelete = $existingQuestions->whereNotIn('id', $submittedQuestionIds);

            if ($questionsToDelete->isNotEmpty()) {
                $idsToDelete = $questionsToDelete->pluck('id')->toArray();

                if (UserAnswer::whereIn('question_id', $idsToDelete)->exists()) {
                    throw ValidationException::withMessages([
                        'questions' => 'Soal yang sudah memiliki jawaban peserta tidak dapat dihapus.',
                    ]);
                }

                Question::whereIn('id', $idsToDelete)->delete();
            }

            foreach ($questionsData as $qData) {
                $questionId = $qData['id'] ?? null;
                
                $question = $questionId 
                    ? $existingQuestions->firstWhere('id', (int) $questionId) 
                    : $lockedTest->questions()->make();

                if ($questionId && ! $question) {
                    throw ValidationException::withMessages([
                        'questions' => 'ID Soal tidak valid atau bukan milik test ini.',
                    ]);
                }

                $question->fill(Arr::only($qData, ['question_text', 'score_weight']))->save();

                $choicesData = collect($qData['choices'] ?? []);
                $submittedChoiceIds = $choicesData->pluck('id')->filter()->map(fn ($id) => (int) $id)->toArray();

                if ($questionId && $question->relationLoaded('choices')) {
                    $existingChoices = $question->choices;
                    $choicesToDelete = $existingChoices->whereNotIn('id', $submittedChoiceIds);

                    if ($choicesToDelete->isNotEmpty()) {
                        $choiceIdsToDelete = $choicesToDelete->pluck('id')->toArray();

                        if (UserAnswer::whereIn('choice_id', $choiceIdsToDelete)->exists()) {
                            throw ValidationException::withMessages([
                                'questions' => 'Pilihan jawaban yang sudah digunakan peserta tidak dapat dihapus.',
                            ]);
                        }

                        Choice::whereIn('id', $choiceIdsToDelete)->delete();
                    }
                }

                foreach ($choicesData as $cData) {
                    $choiceId = $cData['id'] ?? null;
                    
                    $choice = ($choiceId && isset($question->choices))
                        ? $question->choices->firstWhere('id', (int) $choiceId)
                        : $question->choices()->make();

                    $choice->fill(Arr::only($cData, ['choice_text', 'is_correct']))->save();
                }
            }

            return true;
        });
    }

    public function deleteTest(Test $test): bool
    {
        if ($test->events()->exists() || $test->sessions()->exists()) {
            throw ValidationException::withMessages([
                'test' => 'Test yang sudah dipakai pada event atau sesi peserta tidak dapat dihapus.',
            ]);
        }

        return $test->delete();
    }

    private function hasQuestionChanges(Collection $existingQuestions, Collection $submittedQuestions): bool
    {
        if ($existingQuestions->count() !== $submittedQuestions->count()) {
            return true;
        }

        $existingSignature = $existingQuestions->map(function ($q) {
            return [
                'id' => $q->id,
                'text' => $q->question_text,
                'weight' => (int) $q->score_weight,
                'choices' => $q->choices->map(fn ($c) => [
                    'id' => $c->id,
                    'text' => $c->choice_text,
                    'correct' => (bool) $c->is_correct,
                ])->sortBy('id')->values()->toArray(),
            ];
        })->sortBy('id')->values()->toArray();

        $submittedSignature = $submittedQuestions->map(function ($q) {
            return [
                'id' => isset($q['id']) ? (int) $q['id'] : null,
                'text' => $q['question_text'] ?? '',
                'weight' => isset($q['score_weight']) ? (int) $q['score_weight'] : 0,
                'choices' => collect($q['choices'] ?? [])->map(fn ($c) => [
                    'id' => isset($c['id']) ? (int) $c['id'] : null,
                    'text' => $c['choice_text'] ?? '',
                    'correct' => filter_var($c['is_correct'] ?? false, FILTER_VALIDATE_BOOLEAN),
                ])->sortBy('id')->values()->toArray(),
            ];
        })->sortBy('id')->values()->toArray();

        return md5(json_encode($existingSignature)) !== md5(json_encode($submittedSignature));
    }
}
