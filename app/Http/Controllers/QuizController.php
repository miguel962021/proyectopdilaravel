<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateQuizRequest;
use App\Http\Requests\StoreQuizRequest;
use App\Jobs\ProcessQuizAnalysisJob;
use App\Models\Quiz;
use App\Models\QuizAiAnalysis;
use App\Models\QuizInvitation;
use App\Services\QuizAnalyticsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuizController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $quizzes = Quiz::query()
            ->when(
                $user->role !== $user::ROLE_ADMIN,
                fn ($query) => $query->where('user_id', $user->id)
            )
            ->latest()
            ->withCount(['questions', 'attempts'])
            ->paginate(10)
            ->withQueryString();

        return view('quizzes.index', compact('quizzes'));
    }

    public function create(): View
    {
        $quiz = new Quiz([
            'status' => 'draft',
            'max_attempts' => 1,
            'require_login' => true,
        ]);

        return view('quizzes.create', compact('quiz'));
    }

    public function store(StoreQuizRequest $request): RedirectResponse
    {
        $quiz = null;

        DB::transaction(function () use (&$quiz, $request) {
            $quiz = Quiz::create([
                'user_id' => $request->user()->id,
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'status' => $request->input('status', 'draft'),
                'opens_at' => $request->input('opens_at'),
                'closes_at' => $request->input('closes_at'),
                'max_attempts' => $request->input('max_attempts', 1),
                'require_login' => $request->boolean('require_login'),
                'settings' => $request->input('settings'),
            ]);

            // Crear invitación automática si la encuesta está publicada
            if ($quiz->status !== 'draft') {
                $this->ensureDefaultInvitation($quiz);
            }
        });
        return redirect()
            ->route('quizzes.edit', $quiz)
            ->with('status', 'Encuesta creada correctamente. Ahora agrega preguntas y configuraciones.');
    }

    public function show(Quiz $quiz): View
    {
        $this->ensureOwnership($quiz);

        $quiz->load(['questions.options', 'invitations', 'attempts.answers', 'analyses' => fn ($query) => $query->latest()]);

        $quiz->loadCount(['questions', 'attempts']);

        $latestAnalysis = $quiz->analyses->first();

        $questionTypeChart = $this->buildQuestionTypeChart($quiz);
        $invitationUsageChart = $this->buildInvitationUsageChart($quiz);

        $analysisSummary = $this->buildAnalysisSummary($latestAnalysis);

        return view('quizzes.show', [
            'quiz' => $quiz,
            'latestAnalysis' => $latestAnalysis,
            'questionTypeChart' => $questionTypeChart,
            'invitationUsageChart' => $invitationUsageChart,
            'analysisSummary' => $analysisSummary,
        ]);
    }

    public function edit(Quiz $quiz): View
    {
        $this->ensureOwnership($quiz);

        $quiz->load(['questions.options', 'invitations' => fn ($query) => $query->latest()]);

        return view('quizzes.edit', compact('quiz'));
    }

    public function update(UpdateQuizRequest $request, Quiz $quiz): RedirectResponse
    {
        $this->ensureOwnership($quiz);

        $quiz->update($request->validated());

        return redirect()
            ->route('quizzes.edit', $quiz)
            ->with('status', 'Encuesta actualizada correctamente.');
    }

    public function destroy(Quiz $quiz): RedirectResponse
    {
        $this->ensureOwnership($quiz);

        $quiz->delete();

        return redirect()
            ->route('quizzes.index')
            ->with('status', 'Encuesta eliminada correctamente.');
    }

    public function publish(Quiz $quiz): RedirectResponse
    {
        $this->ensureOwnership($quiz);

        if ($quiz->status === 'closed') {
            return back()->with('status', __('La encuesta ya está cerrada.'));
        }

        DB::transaction(function () use ($quiz) {
            $quiz->update([
                'status' => 'published',
                'opens_at' => $quiz->opens_at ?? now(),
            ]);

            $this->ensureDefaultInvitation($quiz);
        });

        return back()->with('status', __('La encuesta se publicó y puede recibir respuestas.'));
    }

    public function close(Quiz $quiz): RedirectResponse
    {
        $this->ensureOwnership($quiz);

        if ($quiz->status === 'closed') {
            return back()->with('status', __('La encuesta ya está cerrada.'));
        }

        $quiz->update([
            'status' => 'closed',
            'closes_at' => now(),
            'analysis_requested_at' => now(),
        ]);

        ProcessQuizAnalysisJob::dispatchSync($quiz->id);

        return back()->with('status', __('La encuesta se cerró y se generó un nuevo análisis con IA.'));
    }

    public function analyze(Quiz $quiz): RedirectResponse
    {
        $this->ensureOwnership($quiz);

        if ($quiz->status !== 'closed') {
            return back()->with('error', __('Debes cerrar la encuesta antes de generar el análisis con IA.'));
        }

        $quiz->update([
            'analysis_requested_at' => now(),
        ]);

        ProcessQuizAnalysisJob::dispatchSync($quiz->id);

        return back()->with('status', __('Se generó un nuevo informe con IA.'));
    }

    public function analysis(Quiz $quiz, QuizAnalyticsService $analyticsService): View
    {
        $this->ensureOwnership($quiz);

        $quiz->load(['questions.options', 'attempts.answers', 'analyses' => fn ($query) => $query->latest()]);

        $analysisRecord = $quiz->analyses->first();
        $analysisSummary = $analysisRecord ? $this->buildAnalysisSummary($analysisRecord) : [
            'summary' => null,
            'completed_at' => null,
            'recommendations' => [],
            'qualitative' => [],
        ];

        $quantitativeInsights = $analyticsService->buildQuantitativeInsights($quiz);
        $qualitativeInsights = $analyticsService->buildQualitativeInsights($quiz);
        $chartConfigs = $analyticsService->buildChartConfigsFromInsights($quantitativeInsights);

        return view('quizzes.analysis', [
            'quiz' => $quiz,
            'analysis' => $analysisRecord,
            'analysisSummary' => $analysisSummary,
            'quantitativeInsights' => $quantitativeInsights,
            'qualitativeInsights' => $qualitativeInsights,
            'chartConfigs' => $chartConfigs,
        ]);
    }

    public function exportAnalysis(Quiz $quiz, QuizAnalyticsService $analyticsService)
    {
        $this->ensureOwnership($quiz);

        $quiz->load(['questions.options', 'attempts.answers', 'analyses' => fn ($query) => $query->latest()]);
        $analysisRecord = $quiz->analyses->first();

        if (! $analysisRecord) {
            return redirect()
                ->route('quizzes.analysis.show', $quiz)
                ->with('error', __('Aún no se ha generado un informe para esta encuesta.'));
        }

        $analysisSummary = $this->buildAnalysisSummary($analysisRecord);
        $quantitativeInsights = $analyticsService->buildQuantitativeInsights($quiz);
        $qualitativeInsights = $analyticsService->buildQualitativeInsights($quiz);

        $pdf = Pdf::loadView('quizzes.analysis-pdf', [
            'quiz' => $quiz,
            'analysis' => $analysisRecord,
            'analysisSummary' => $analysisSummary,
            'quantitativeInsights' => $quantitativeInsights,
            'qualitativeInsights' => $qualitativeInsights,
        ])->setPaper('a4');

        $filename = 'informe-' . Str::slug($quiz->title ?? 'encuesta') . '-' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }

    protected function ensureDefaultInvitation(Quiz $quiz): void
    {
        if ($quiz->invitations()->exists()) {
            return;
        }

        $quiz->invitations()->create([
            'created_by' => Auth::id(),
            'code' => $this->generateInvitationCode(),
            'label' => __('Código inicial'),
            'is_active' => true,
        ]);
    }

    protected function generateInvitationCode(?string $preferred = null): string
    {
        $code = Str::upper($preferred ?: Str::random(8));

        while (QuizInvitation::where('code', $code)->exists()) {
            $code = Str::upper(Str::random(8));
        }

        return $code;
    }

    protected function ensureOwnership(Quiz $quiz): void
    {
        $user = Auth::user();

        abort_if(
            $user->role !== $user::ROLE_ADMIN && $quiz->user_id !== $user->id,
            403,
            'No tienes permisos para acceder a esta encuesta.'
        );
    }

    protected function buildQuestionTypeChart(Quiz $quiz): array
    {
        $typeLabels = [
            'multiple_choice' => __('Opción múltiple'),
            'multi_select' => __('Selección múltiple'),
            'scale' => __('Escala'),
            'open_text' => __('Respuesta abierta'),
            'numeric' => __('Respuesta numérica'),
        ];

        $grouped = $quiz->questions->groupBy('type');

        $labels = $grouped->map(function ($questions, $type) use ($typeLabels) {
            return $typeLabels[$type] ?? ucfirst(str_replace('_', ' ', $type));
        })->values()->all();

        $data = $grouped->map->count()->values()->all();

        return compact('labels', 'data');
    }

    protected function buildInvitationUsageChart(Quiz $quiz): array
    {
        $labels = $quiz->invitations
            ->map(fn (QuizInvitation $invitation) => $invitation->label ?: $invitation->code)
            ->values()
            ->all();

        $data = $quiz->invitations
            ->map(fn (QuizInvitation $invitation) => $invitation->uses_count)
            ->values()
            ->all();

        return compact('labels', 'data');
    }

    protected function buildAnalysisSummary(?QuizAiAnalysis $analysis): array
    {
        if (! $analysis) {
            return [
                'status' => null,
                'summary' => null,
                'quantitative' => [],
                'qualitative' => [],
                'recommendations' => [],
                'completed_at' => null,
                'error_message' => null,
            ];
        }

        return [
            'status' => $analysis->status,
            'summary' => $analysis->summary,
            'quantitative' => $this->normalizeQuantitative($analysis->quantitative_insights),
            'qualitative' => $this->normalizeQualitative($analysis->qualitative_themes),
            'recommendations' => $this->normalizeRecommendations($analysis->recommendations),
            'completed_at' => $analysis->completed_at,
            'error_message' => $analysis->error_message,
        ];
    }

    protected function normalizeQuantitative(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return collect($value)
            ->filter(fn ($item) => is_array($item) && isset($item['question']))
            ->map(function (array $item) {
                $item['key_findings'] = array_values(array_filter(
                    array_map('trim', (array) ($item['key_findings'] ?? []))
                ));

                return $item;
            })
            ->values()
            ->all();
    }

    protected function normalizeQualitative(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return collect($value)
            ->filter(fn ($item) => is_array($item) && isset($item['theme']))
            ->map(function (array $item) {
                $item['evidence'] = array_values(array_filter(
                    array_map('trim', (array) ($item['evidence'] ?? []))
                ));

                return $item;
            })
            ->values()
            ->all();
    }

    protected function normalizeRecommendations(mixed $value): array
    {
        if (is_string($value)) {
            $lines = preg_split('/[\r\n]+/', $value) ?: [];

            return array_values(array_filter(array_map('trim', $lines)));
        }

        if (is_array($value)) {
            return array_values(array_filter(array_map('trim', $value)));
        }

        return [];
    }
}
