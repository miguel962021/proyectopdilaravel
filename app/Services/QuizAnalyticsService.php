<?php

namespace App\Services;

use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use Illuminate\Support\Collection;

class QuizAnalyticsService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function buildQuantitativeInsights(Quiz $quiz): array
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
    public function buildQualitativeInsights(Quiz $quiz): array
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
                ->limit(50)
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
     * @return array<int, array<string, mixed>>
     */
    public function buildChartConfigs(Quiz $quiz): array
    {
        return $this->buildChartConfigsFromInsights($this->buildQuantitativeInsights($quiz));
    }

    /**
     * @param array<int, array<string, mixed>> $insights
     * @return array<int, array<string, mixed>>
     */
    public function buildChartConfigsFromInsights(array $insights): array
    {
        $configs = [];

        foreach ($insights as $insight) {
            $chart = $this->chartConfigFromInsight($insight);

            if ($chart) {
                $configs[] = $chart;
            }
        }

        return $configs;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function chartConfigFromInsight(array $insight): ?array
    {
        $type = $insight['type'] ?? null;

        return match ($type) {
            'multiple_choice' => $this->buildPieChart($insight),
            'multi_select' => $this->buildBarChart($insight),
            'scale', 'numeric' => $this->buildBarChart($insight, true),
            default => null,
        };
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
            ->filter(fn ($value) => $value !== null)
            ->map(fn ($value) => (float) $value)
            ->values();

        if ($values->isEmpty()) {
            return $this->summarizeGeneric($question, $answers);
        }

        $total = $values->count();

        $distribution = $values
            ->groupBy(fn ($value) => (string) $value)
            ->map(fn (Collection $items, $key) => [
                'value' => (float) $key,
                'count' => $items->count(),
                'percentage' => $total > 0 ? round(($items->count() / $total) * 100, 2) : 0,
            ])
            ->values()
            ->all();

        return [
            'question_id' => $question->id,
            'question' => $question->title,
            'type' => $question->type,
            'total_responses' => $total,
            'average' => round($values->avg(), 2),
            'minimum' => $values->min(),
            'maximum' => $values->max(),
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

    /**
     * @return array<string, mixed>
     */
    protected function buildPieChart(array $insight): array
    {
        $labels = collect($insight['options'] ?? [])->pluck('label')->all();
        $data = collect($insight['options'] ?? [])->pluck('count')->all();

        return [
            'question_id' => $insight['question_id'],
            'question' => $insight['question'],
            'type' => 'pie',
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildBarChart(array $insight, bool $isNumeric = false): array
    {
        if ($isNumeric) {
            $labels = collect($insight['distribution'] ?? [])->map(fn ($item) => (string) ($item['value'] ?? ''))->all();
            $data = collect($insight['distribution'] ?? [])->pluck('count')->all();
        } else {
            $labels = collect($insight['options'] ?? [])->pluck('label')->all();
            $data = collect($insight['options'] ?? [])->pluck('count')->all();
        }

        return [
            'question_id' => $insight['question_id'],
            'question' => $insight['question'],
            'type' => 'bar',
            'labels' => $labels,
            'data' => $data,
        ];
    }
}
