<?php

namespace App\Jobs;

use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAiAnalysis;
use App\Models\QuizAnswer;
use App\Services\OpenAIService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Throwable;

class ProcessQuizAnalysisJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly int $quizId
    ) {
    }

    public function handle(OpenAIService $openAI): void
    {
        /** @var Quiz|null $quiz */
        $quiz = Quiz::with(['questions.options', 'attempts.answers'])
            ->find($this->quizId);

        if (! $quiz) {
            return;
        }

        if ($quiz->status !== 'closed' || ! $quiz->analysis_requested_at) {
            return;
        }

        $analysis = $quiz->analyses()->create([
            'status' => 'processing',
            'started_at' => now(),
        ]);

        try {
            $quantitative = $this->buildQuantitativeInsights($quiz);
            $qualitative = $this->buildQualitativeSamples($quiz);
            $meta = $this->buildSurveyMeta($quiz, $quantitative);

            $promptPayload = [
                'meta' => $meta,
                'quantitative' => $quantitative,
                'qualitative_samples' => $qualitative,
            ];

            $prompt = $this->buildPrompt($promptPayload);

            $response = $openAI->chat($prompt, [
                'temperature' => 0.2,
                'max_tokens' => 900,
            ]);

            if (! $response) {
                throw new \RuntimeException('La respuesta de OpenAI fue vacía.');
            }

            $decoded = json_decode($response, true);

            if (! is_array($decoded)) {
                $decoded = null;
            }

            $analysis->update([
                'status' => 'completed',
                'summary' => Arr::get($decoded, 'summary') ?? $response,
                'recommendations' => Arr::get($decoded, 'recommendations'),
                'quantitative_insights' => Arr::get($decoded, 'quantitative_insights', $quantitative),
                'qualitative_themes' => Arr::get($decoded, 'qualitative_themes'),
                'raw_response' => $decoded ?? ['raw' => $response],
                'completed_at' => now(),
            ]);

            $quiz->update([
                'analysis_completed_at' => now(),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            $analysis->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
                'raw_response' => null,
            ]);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function buildQuantitativeInsights(Quiz $quiz): array
    {
        $results = [];

        foreach ($quiz->questions as $question) {
            $answers = QuizAnswer::query()
                ->where('question_id', $question->id)
                ->get();

            if ($answers->isEmpty()) {
                continue;
            }

            $results[] = match ($question->type) {
                'multiple_choice' => $this->summarizeMultipleChoice($question, $answers),
                'multi_select' => $this->summarizeMultiSelect($question, $answers),
                'scale', 'numeric' => $this->summarizeNumeric($question, $answers),
                default => $this->summarizeGeneric($question, $answers),
            };
        }

        return $results;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function buildQualitativeSamples(Quiz $quiz): array
    {
        $samples = [];

        foreach ($quiz->questions as $question) {
            if ($question->type !== 'open_text') {
                continue;
            }

            $responses = QuizAnswer::query()
                ->where('question_id', $question->id)
                ->whereNotNull('answer_text')
                ->whereRaw("TRIM(answer_text) <> ''")
                ->orderByDesc('created_at')
                ->limit(25)
                ->pluck('answer_text')
                ->map(fn ($value) => mb_substr($value, 0, 500))
                ->values();

            if ($responses->isEmpty()) {
                continue;
            }

            $samples[] = [
                'question_id' => $question->id,
                'question' => $question->title,
                'responses' => $responses->all(),
            ];
        }

        return $samples;
    }

    /**
     * @param array<string, mixed> $quantitative
     * @return array<string, mixed>
     */
    protected function buildSurveyMeta(Quiz $quiz, array $quantitative): array
    {
        return [
            'quiz_id' => $quiz->id,
            'title' => $quiz->title,
            'description' => $quiz->description,
            'owner' => $quiz->owner?->name,
            'total_attempts' => $quiz->attempts->count(),
            'total_questions' => $quiz->questions->count(),
            'quantitative_questions' => collect($quantitative)->pluck('question')->all(),
            'closed_at' => optional($quiz->closes_at)->toDateTimeString(),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    protected function buildPrompt(array $payload): string
    {
        $instructions = <<<PROMPT
Eres un analista pedagógico y debes interpretar los resultados de una encuesta educativa.

Revisa los datos cuantitativos y las respuestas abiertas incluidas en el JSON más abajo.
Devuelve tu respuesta en formato JSON válido con la siguiente estructura:
{
  "summary": "Resumen ejecutivo de máximo 4 oraciones",
  "quantitative_insights": [
    {
      "question": "Título de la pregunta",
      "key_findings": ["hallazgo cuantitativo 1", "hallazgo cuantitativo 2"]
    }
  ],
  "qualitative_themes": [
    {
      "theme": "Tema detectado",
      "evidence": ["cita corta 1", "cita corta 2"]
    }
  ],
  "recommendations": [
    "Recomendación pedagógica 1",
    "Recomendación pedagógica 2"
  ]
}

Usa un tono profesional y positivo. Si falta información para una sección, devuelve un arreglo vacío para esa sección en lugar de inventar datos.
PROMPT;

        return $instructions.PHP_EOL.PHP_EOL.json_encode($payload, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return array<string, mixed>
     */
    protected function summarizeMultipleChoice(Question $question, Collection $answers): array
    {
        $total = $answers->count();

        $optionCounts = $answers
            ->groupBy('question_option_id')
            ->map(fn (Collection $items) => $items->count());

        $options = $question->options->map(function ($option) use ($optionCounts, $total) {
            $count = $optionCounts->get($option->id, 0);

            return [
                'option_id' => $option->id,
                'label' => $option->label,
                'count' => $count,
                'percentage' => $total > 0 ? round(($count / $total) * 100, 2) : 0,
            ];
        })->all();

        return [
            'question_id' => $question->id,
            'question' => $question->title,
            'type' => $question->type,
            'total_responses' => $total,
            'options' => $options,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function summarizeMultiSelect(Question $question, Collection $answers): array
    {
        $optionCounts = $answers
            ->groupBy('question_option_id')
            ->map(fn (Collection $items) => $items->count());

        $options = $question->options->map(function ($option) use ($optionCounts) {
            $count = $optionCounts->get($option->id, 0);

            return [
                'option_id' => $option->id,
                'label' => $option->label,
                'count' => $count,
            ];
        })->all();

        $distinctRespondents = $answers
            ->pluck('attempt_id')
            ->filter()
            ->unique()
            ->count();

        return [
            'question_id' => $question->id,
            'question' => $question->title,
            'type' => $question->type,
            'total_responses' => $distinctRespondents,
            'options' => $options,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function summarizeNumeric(Question $question, Collection $answers): array
    {
        $values = $answers
            ->pluck('answer_number')
            ->filter(function ($value) {
                return $value !== null;
            })
            ->map(fn ($value) => (float) $value)
            ->values();

        if ($values->isEmpty()) {
            return $this->summarizeGeneric($question, $answers);
        }

        $average = round($values->avg(), 2);
        $min = $values->min();
        $max = $values->max();

        $distribution = $values
            ->groupBy(fn ($value) => (string) $value)
            ->map(fn (Collection $items, $key) => [
                'value' => (float) $key,
                'count' => $items->count(),
                'percentage' => $values->count() > 0
                    ? round(($items->count() / $values->count()) * 100, 2)
                    : 0,
            ])
            ->values()
            ->all();

        return [
            'question_id' => $question->id,
            'question' => $question->title,
            'type' => $question->type,
            'total_responses' => $values->count(),
            'average' => $average,
            'minimum' => $min,
            'maximum' => $max,
            'distribution' => $distribution,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function summarizeGeneric(Question $question, Collection $answers): array
    {
        return [
            'question_id' => $question->id,
            'question' => $question->title,
            'type' => $question->type,
            'total_responses' => $answers->count(),
        ];
    }
}
