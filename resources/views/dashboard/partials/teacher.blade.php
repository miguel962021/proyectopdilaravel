<div class="row">
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">{{ __('Encuestas creadas') }}</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_surveys'] ?? 0) }}</div>
                    </div>
                    <span class="text-primary"><i class="fas fa-layer-group fa-2x"></i></span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">{{ __('Publicadas') }}</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['published_surveys'] ?? 0) }}</div>
                    </div>
                    <span class="text-success"><i class="fas fa-bullhorn fa-2x"></i></span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">{{ __('Respuestas registradas') }}</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['responses'] ?? 0) }}</div>
                    </div>
                    <span class="text-info"><i class="fas fa-chart-line fa-2x"></i></span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">{{ __('Invitaciones activas') }}</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['active_invitations'] ?? 0) }}</div>
                    </div>
                    <span class="text-warning"><i class="fas fa-key fa-2x"></i></span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">{{ __('Actividad semanal de mis encuestas') }}</h6>
                <span class="badge badge-light text-muted text-uppercase">{{ __('Demo') }}</span>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    @if (! empty($charts['usage']))
                        {!! $charts['usage']->container() !!}
                    @else
                        <p class="text-muted small mb-0">{{ __('Aún no hay respuestas registradas en esta semana.') }}</p>
                    @endif
                </div>
                <p class="text-muted small mt-3 mb-0">
                    {{ __('Respuestas que has recibido día a día durante los últimos 7 días en todas tus encuestas.') }}
                </p>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">{{ __('Estado de tus encuestas') }}</h6>
            </div>
            <div class="card-body">
                <div class="chart-pie pt-2 pb-3">
                    @if (! empty($charts['status']))
                        {!! $charts['status']->container() !!}
                    @else
                        <p class="text-muted small mb-0">{{ __('No hay suficientes datos para mostrar este gráfico.') }}</p>
                    @endif
                </div>
                <ul class="list-unstyled text-muted small mb-0">
                    <li class="mb-2"><i class="fas fa-circle text-success mr-2"></i>{{ __('Publicadas') }}: <strong>{{ number_format($stats['published_surveys'] ?? 0) }}</strong></li>
                    <li class="mb-2"><i class="fas fa-circle text-secondary mr-2"></i>{{ __('Borradores') }}: <strong>{{ number_format($stats['draft_surveys'] ?? 0) }}</strong></li>
                    <li class="mb-2"><i class="fas fa-circle text-primary mr-2"></i>{{ __('Cerradas') }}: <strong>{{ number_format($stats['closed_surveys'] ?? 0) }}</strong></li>
                </ul>
                <p class="text-muted small mt-3 mb-0">
                    {{ __('Sigue el estado de cada encuesta para decidir cuándo publicar, cerrar o solicitar análisis de IA.') }}
                </p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">{{ __('Encuestas recientes') }}</h6>
                <a href="{{ route('quizzes.index') }}" class="small text-primary">{{ __('Ver todas') }}</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ __('Título') }}</th>
                                <th class="text-center">{{ __('Preguntas') }}</th>
                                <th class="text-center">{{ __('Respuestas') }}</th>
                                <th class="text-right">{{ __('Estatus') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentSurveys as $survey)
                                <tr>
                                    <td>
                                        <a href="{{ route('quizzes.show', $survey) }}" class="font-weight-semibold text-gray-800">
                                            {{ $survey->title }}
                                        </a>
                                    </td>
                                    <td class="text-center">{{ $survey->questions_count }}</td>
                                    <td class="text-center">{{ $survey->attempts_count }}</td>
                                    <td class="text-right">
                                        <span class="badge badge-pill badge-light text-uppercase">{{ __($survey->status) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">{{ __('Aún no has creado encuestas.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">{{ __('Respuestas más recientes') }}</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ __('Encuesta') }}</th>
                                <th>{{ __('Participante') }}</th>
                                <th class="text-right">{{ __('Fecha') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentResponses as $attempt)
                                <tr>
                                    <td>{{ $attempt->quiz?->title ?? __('Encuesta eliminada') }}</td>
                                    <td class="text-muted small">{{ $attempt->participant_name ?? $attempt->participant_email ?? __('Anónimo') }}</td>
                                    <td class="text-right text-muted small">{{ optional($attempt->completed_at ?? $attempt->created_at)->format('d/m/Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">{{ __('Todavía no hay respuestas registradas.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">{{ __('Análisis pendientes de IA') }}</h6>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse ($pendingAnalysis as $quiz)
                        <a href="{{ route('quizzes.show', $quiz) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1 font-weight-semibold text-gray-800">{{ $quiz->title }}</h6>
                                <p class="mb-0 text-muted small">
                                    {{ __('Solicitado el :date', ['date' => optional($quiz->analysis_requested_at)->format('d/m/Y H:i')]) }}
                                </p>
                            </div>
                            <span class="badge badge-pill badge-warning text-uppercase">{{ __('Pendiente') }}</span>
                        </a>
                    @empty
                        <div class="list-group-item text-center text-muted py-4">
                            {{ __('No tienes análisis de IA pendientes.') }}
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">{{ __('Recomendaciones rápidas') }}</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled text-muted small mb-0">
                    <li class="mb-3"><i class="fas fa-magic text-primary mr-2"></i>{{ __('Solicita el análisis con IA tras cerrar una encuesta para obtener recomendaciones inmediatas.') }}</li>
                    <li class="mb-3"><i class="fas fa-share-alt text-success mr-2"></i>{{ __('Comparte tus códigos de invitación activos para favorecer la participación de los estudiantes.') }}</li>
                    <li class="mb-0"><i class="fas fa-clipboard-check text-info mr-2"></i>{{ __('Revisa las respuestas recientes para dar seguimiento a estudiantes que necesiten acompañamiento.') }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>
