<?php

namespace App\Jobs;

use App\Models\Quiz;
use App\Models\QuizAiAnalysis;
use App\Services\OpenAIService;
use App\Services\QuizAnalyticsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
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

    public function handle(OpenAIService $openAI, QuizAnalyticsService $analyticsService): void
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
            $quantitative = $analyticsService->buildQuantitativeInsights($quiz);
            $qualitative = $analyticsService->buildQualitativeInsights($quiz);
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

            $content = data_get($response, 'choices.0.message.content');

            if (! is_string($content) || blank($content)) {
                throw new \RuntimeException('La respuesta de OpenAI no contenía texto utilizable.');
            }

            $decoded = json_decode($content, true);

            if (! is_array($decoded)) {
                $decoded = null;
            }

            $analysis->update([
                'status' => 'completed',
                'summary' => Arr::get($decoded, 'summary') ?? $content,
                'recommendations' => Arr::get($decoded, 'recommendations'),
                'quantitative_insights' => Arr::get($decoded, 'quantitative_insights', $quantitative),
                'qualitative_themes' => Arr::get($decoded, 'qualitative_themes'),
                'raw_response' => $decoded ?? ['raw' => $content],
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
}
