<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizInvitation;
use App\Models\User;
use ArielMejiaDev\LarapexCharts\LarapexChart;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        $role = $user->role;

        return match ($role) {
            User::ROLE_ADMIN => $this->adminDashboard(),
            User::ROLE_TEACHER => $this->teacherDashboard($user),
            User::ROLE_STUDENT => $this->studentDashboard($user),
            default => $this->studentDashboard($user),
        };
    }

    protected function adminDashboard(): View
    {
        $stats = [
            'users' => User::count(),
            'surveys' => Quiz::count(),
            'attempts' => QuizAttempt::count(),
            'invites' => QuizInvitation::where('is_active', true)->count(),
        ];

        $roleCounts = User::query()
            ->selectRaw('role, COUNT(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role')
            ->toArray();

        $recentUsers = User::latest()->take(5)->get();

        $attemptSeries = $this->buildAttemptSeries(QuizAttempt::query());

        return view('dashboard', [
            'role' => User::ROLE_ADMIN,
            'stats' => $stats,
            'roleCounts' => $roleCounts,
            'recentUsers' => $recentUsers,
            'charts' => array_filter([
                'usage' => $this->buildLineChart(
                    $attemptSeries,
                    __('Actividad semanal de respuestas'),
                    __('Respuestas completadas')
                ),
                'roles' => $this->buildDonutChart(
                    [
                        __('Administradores'),
                        __('Docentes'),
                        __('Estudiantes'),
                    ],
                    [
                        $roleCounts['administrador'] ?? 0,
                        $roleCounts['docente'] ?? 0,
                        $roleCounts['estudiante'] ?? 0,
                    ],
                    __('Distribución por rol')
                ),
                'participation' => $this->buildDonutChart(
                    [
                        __('Invitaciones directas'),
                        __('Docentes compartieron'),
                        __('Accesos externos'),
                    ],
                    [55, 30, 15],
                    __('Fuentes de participación')
                ),
            ]),
        ]);
    }

    protected function teacherDashboard(User $user): View
    {
        $quizIds = $user->quizzes()->pluck('id');

        $surveysCount = $quizIds->count();

        $publishedCount = $user->quizzes()
            ->where('status', 'published')
            ->count();

        $draftCount = $user->quizzes()
            ->where('status', 'draft')
            ->count();

        $closedCount = $user->quizzes()
            ->where('status', 'closed')
            ->count();

        $attemptsCount = QuizAttempt::whereIn('quiz_id', $quizIds)->count();

        $activeInvitations = QuizInvitation::whereIn('quiz_id', $quizIds)
            ->where('is_active', true)
            ->count();

        $recentSurveys = $user->quizzes()
            ->withCount(['questions', 'attempts'])
            ->latest()
            ->take(5)
            ->get();

        $recentResponses = QuizAttempt::with('quiz')
            ->whereIn('quiz_id', $quizIds)
            ->latest()
            ->take(5)
            ->get();

        $pendingAnalysis = $user->quizzes()
            ->where('status', 'closed')
            ->whereNotNull('analysis_requested_at')
            ->whereNull('analysis_completed_at')
            ->latest('analysis_requested_at')
            ->take(5)
            ->get();

        $attemptSeries = $this->buildAttemptSeries(
            QuizAttempt::query()->whereIn('quiz_id', $quizIds)
        );

        return view('dashboard', [
            'role' => User::ROLE_TEACHER,
            'stats' => [
                'total_surveys' => $surveysCount,
                'published_surveys' => $publishedCount,
                'draft_surveys' => $draftCount,
                'closed_surveys' => $closedCount,
                'responses' => $attemptsCount,
                'active_invitations' => $activeInvitations,
            ],
            'recentSurveys' => $recentSurveys,
            'recentResponses' => $recentResponses,
            'pendingAnalysis' => $pendingAnalysis,
            'charts' => array_filter([
                'usage' => $this->buildLineChart(
                    $attemptSeries,
                    __('Actividad semanal de mis encuestas'),
                    __('Respuestas registradas')
                ),
                'status' => $this->buildDonutChart(
                    [
                        __('Publicadas'),
                        __('Borradores'),
                        __('Cerradas'),
                    ],
                    [
                        $publishedCount,
                        $draftCount,
                        $closedCount,
                    ],
                    __('Estado de tus encuestas')
                ),
            ]),
        ]);
    }

    protected function studentDashboard(User $user): View
    {
        $completedAttempts = $user->quizAttempts()->count();

        $recentAttempts = $user->quizAttempts()
            ->with('quiz')
            ->latest()
            ->take(5)
            ->get();

        $lastAttemptAt = optional($user->quizAttempts()->latest('completed_at')->first())->completed_at;

        $availableSurveys = Quiz::where('status', 'published')
            ->where(function (Builder $query) use ($user) {
                $query->where('require_login', false)
                    ->orWhereHas('attempts', function (Builder $attemptQuery) use ($user) {
                        $attemptQuery->where('user_id', $user->id);
                    });
            })
            ->count();

        $attemptSeries = $this->buildAttemptSeries(
            QuizAttempt::query()->where('user_id', $user->id)
        );

        return view('dashboard', [
            'role' => User::ROLE_STUDENT,
            'stats' => [
                'available_surveys' => $availableSurveys,
                'completed_surveys' => $completedAttempts,
                'last_activity' => $lastAttemptAt,
            ],
            'recentAttempts' => $recentAttempts,
            'charts' => array_filter([
                'usage' => $this->buildLineChart(
                    $attemptSeries,
                    __('Mi actividad semanal'),
                    __('Encuestas completadas')
                ),
            ]),
        ]);
    }

    protected function buildAttemptSeries(Builder $query): array
    {
        $period = CarbonPeriod::create(
            Carbon::now()->subDays(6)->startOfDay(),
            Carbon::now()->endOfDay()
        );

        $labels = [];
        $values = [];

        foreach ($period as $date) {
            $labels[] = $date->translatedFormat('D d');

            $values[] = (clone $query)
                ->whereBetween('created_at', [$date->startOfDay(), $date->endOfDay()])
                ->count();
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    /**
     * @param array{labels: array<int, string>, values: array<int, int>} $series
     */
    /**
     * @return \ArielMejiaDev\LarapexCharts\LineChart|null
     */
    protected function buildLineChart(array $series, string $title, string $datasetLabel)
    {
        if (empty($series['labels'])) {
            return null;
        }

        $chart = (new LarapexChart())->lineChart();

        $chart
            ->setTitle($title)
            ->setHeight(320)
            ->setColors(['#4e73df'])
            ->setMarkers(['#2e59d9'], 7, 10)
            ->setXAxis($series['labels'])
            ->addData($datasetLabel, $series['values']);

        return $chart;
    }

    /**
     * @return \ArielMejiaDev\LarapexCharts\DonutChart|null
     */
    protected function buildDonutChart(array $labels, array $values, string $title)
    {
        if (! array_sum($values)) {
            return null;
        }

        $chart = (new LarapexChart())->donutChart();

        $chart
            ->setTitle($title)
            ->setHeight(320)
            ->setLabels($labels)
            ->addData($values)
            ->setColors(['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796']);

        return $chart;
    }
}
