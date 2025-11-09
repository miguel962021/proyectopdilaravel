<x-app-layout>
    <x-slot name="header">
        {{ __('Detalles de la encuesta') }}
    </x-slot>

    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">{{ $quiz->title }}</h6>
                    @php
                        $statusLabels = [
                            'draft' => __('Borrador'),
                            'published' => __('Publicada'),
                            'closed' => __('Cerrada'),
                        ];
                    @endphp
                    <span class="badge badge-pill badge-light text-secondary text-uppercase">
                        {{ $statusLabels[$quiz->status] ?? ucfirst($quiz->status) }}
                    </span>
                </div>
                <div class="card-body">
                    @if ($quiz->description)
                        <p class="mb-4 text-muted">{{ $quiz->description }}</p>
                    @endif

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="small text-muted">{{ __('Respuestas registradas') }}</div>
                            <div class="h4 font-weight-bold">{{ $quiz->attempts->count() }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-muted">{{ __('Preguntas disponibles') }}</div>
                            <div class="h4 font-weight-bold">{{ $quiz->questions->count() }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-muted">{{ __('Requiere autenticación') }}</div>
                            <div class="h4 font-weight-bold">
                                <span class="badge {{ $quiz->require_login ? 'badge-success' : 'badge-secondary' }}">
                                    {{ $quiz->require_login ? __('Sí') : __('No') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <h6 class="font-weight-bold text-secondary text-uppercase">{{ __('Periodo de disponibilidad') }}</h6>
                    <div class="mb-4">
                        <p class="mb-1"><strong>{{ __('Desde:') }}</strong> {{ $quiz->opens_at ? $quiz->opens_at->format('d/m/Y H:i') : __('Sin definir') }}</p>
                        <p class="mb-0"><strong>{{ __('Hasta:') }}</strong> {{ $quiz->closes_at ? $quiz->closes_at->format('d/m/Y H:i') : __('Sin definir') }}</p>
                    </div>

                    <h6 class="font-weight-bold text-secondary text-uppercase">{{ __('Instrucciones adicionales') }}</h6>
                    <p class="text-muted">
                        {{ data_get($quiz->settings, 'additional_instructions') ?? __('No se han definido instrucciones adicionales.') }}
                    </p>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('Códigos de invitación') }}</h6>
                </div>
                <div class="card-body">
                    @if ($quiz->invitations->count())
                        <ul class="list-group">
                            @foreach ($quiz->invitations as $invitation)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $invitation->code }}</strong>
                                        <div class="small text-muted">
                                            {{ __('Usos') }}: {{ $invitation->uses_count }}{{ $invitation->max_uses ? ' / '.$invitation->max_uses : '' }}
                                        </div>
                                        @if ($invitation->expires_at)
                                            <div class="small text-muted">{{ __('Expira:') }} {{ $invitation->expires_at->format('d/m/Y H:i') }}</div>
                                        @endif
                                    </div>
                                    <span class="badge {{ $invitation->is_active ? 'badge-success' : 'badge-secondary' }}">
                                        {{ $invitation->is_active ? __('Activo') : __('Inactivo') }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted small mb-0">{{ __('Sin invitaciones generadas aún.') }}</p>
                    @endif
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('Acciones rápidas') }}</h6>
                </div>
                <div class="card-body">
                    <a href="{{ route('quizzes.edit', $quiz) }}" class="btn btn-primary btn-block mb-2">
                        <i class="fas fa-edit mr-1"></i>{{ __('Editar encuesta') }}
                    </a>
                    @if ($quiz->status === 'closed')
                        <form method="POST" action="{{ route('quizzes.analyze', $quiz) }}" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fas fa-robot mr-1"></i>{{ __('Regenerar análisis con IA') }}
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('quizzes.index') }}" class="btn btn-outline-secondary btn-block">
                        <i class="fas fa-arrow-left mr-1"></i>{{ __('Volver al listado') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('Resumen de respuestas') }}</h6>
                    <span class="badge badge-info badge-pill">{{ __('Encuesta') }}</span>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-4">
                        <div class="col-sm-4 mb-3 mb-sm-0">
                            <div class="text-xs font-weight-bold text-uppercase text-muted">{{ __('Respuestas totales') }}</div>
                            <div class="h3 font-weight-bold text-gray-800">{{ $quiz->attempts->count() }}</div>
                        </div>
                        <div class="col-sm-4 mb-3 mb-sm-0">
                            <div class="text-xs font-weight-bold text-uppercase text-muted">{{ __('Preguntas abiertas') }}</div>
                            <div class="h3 font-weight-bold text-gray-800">{{ $quiz->questions->where('type', 'open_text')->count() }}</div>
                        </div>
                        <div class="col-sm-4">
                            <div class="text-xs font-weight-bold text-uppercase text-muted">{{ __('Tiempo promedio (pendiente)') }}</div>
                            <div class="h3 font-weight-bold text-gray-800">—</div>
                        </div>
                    </div>

                    <h6 class="font-weight-bold text-secondary text-uppercase mb-3">{{ __('Distribución por tipo de pregunta') }}</h6>
                    <div>
                        <canvas id="questionTypeChart" height="120"></canvas>
                    </div>

                    <h6 class="font-weight-bold text-secondary text-uppercase mt-4 mb-3">{{ __('Participación por invitación') }}</h6>
                    <div>
                        <canvas id="invitationUsageChart" height="120"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('Análisis con IA') }}</h6>
                    @if ($analysisSummary['status'])
                        @php
                            $statusClasses = [
                                'pending' => 'badge-warning',
                                'processing' => 'badge-info',
                                'completed' => 'badge-success',
                                'failed' => 'badge-danger',
                            ];
                            $statusTexts = [
                                'pending' => __('Pendiente'),
                                'processing' => __('Procesando'),
                                'completed' => __('Completado'),
                                'failed' => __('Error'),
                            ];
                            $badgeClass = $statusClasses[$analysisSummary['status']] ?? 'badge-secondary';
                            $badgeText = $statusTexts[$analysisSummary['status']] ?? ucfirst($analysisSummary['status']);
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $badgeText }}</span>
                    @endif
                </div>
                <div class="card-body">
                    @if ($analysisSummary['status'] === 'completed')
                        @if ($analysisSummary['completed_at'])
                            <p class="text-muted small mb-3">
                                <i class="fas fa-clock mr-1"></i>{{ __('Generado el :date', ['date' => $analysisSummary['completed_at']->format('d/m/Y H:i')]) }}
                            </p>
                        @endif

                        @if ($analysisSummary['summary'])
                            <p class="font-weight-semibold text-gray-800 mb-3">{{ $analysisSummary['summary'] }}</p>
                        @endif

                        @if (! empty($analysisSummary['quantitative']))
                            <h6 class="text-secondary text-uppercase small font-weight-bold">{{ __('Hallazgos cuantitativos') }}</h6>
                            <ul class="list-unstyled small text-muted mb-3">
                                @foreach ($analysisSummary['quantitative'] as $finding)
                                    <li class="mb-2">
                                        <strong>{{ $finding['question'] }}</strong>
                                        @if (! empty($finding['key_findings']))
                                            <ul class="mt-1 mb-0 pl-3">
                                                @foreach ($finding['key_findings'] as $item)
                                                    <li>{{ $item }}</li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        @if (! empty($analysisSummary['qualitative']))
                            <h6 class="text-secondary text-uppercase small font-weight-bold">{{ __('Temas cualitativos') }}</h6>
                            <div class="small text-muted mb-3">
                                @foreach ($analysisSummary['qualitative'] as $theme)
                                    <div class="mb-2">
                                        <strong class="d-block">{{ $theme['theme'] }}</strong>
                                        @if (! empty($theme['evidence']))
                                            <ul class="pl-3 mb-0 mt-1">
                                                @foreach ($theme['evidence'] as $quote)
                                                    <li>“{{ $quote }}”</li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if (! empty($analysisSummary['recommendations']))
                            <h6 class="text-secondary text-uppercase small font-weight-bold">{{ __('Recomendaciones') }}</h6>
                            <ul class="small text-muted mb-0">
                                @foreach ($analysisSummary['recommendations'] as $recommendation)
                                    <li>{{ $recommendation }}</li>
                                @endforeach
                            </ul>
                        @endif
                    @elseif ($quiz->status === 'closed')
                        @if ($analysisSummary['status'] === 'failed')
                            <div class="alert alert-danger small" role="alert">
                                <i class="fas fa-exclamation-triangle mr-1"></i>{{ __('Hubo un problema generando el informe de IA.') }}
                                @if ($analysisSummary['error_message'])
                                    <div class="mt-1 text-monospace">{{ $analysisSummary['error_message'] }}</div>
                                @endif
                                <div class="mt-2 mb-0">
                                    {{ __('Intenta regenerar el análisis con el botón superior.') }}
                                </div>
                            </div>
                        @else
                            <p class="text-muted small mb-0">
                                <i class="fas fa-hourglass-half mr-1"></i>{{ __('Estamos recopilando resultados. Vuelve a intentarlo en unos instantes o regenera el análisis.') }}
                            </p>
                        @endif
                    @else
                        <p class="text-muted small mb-0">
                            {{ __('Cierra la encuesta para habilitar un informe automático con IA y recibir recomendaciones personalizadas.') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const questionTypeData = @json($questionTypeChart);
        const typeChartCtx = document.getElementById('questionTypeChart');
        if (typeChartCtx && questionTypeData.labels.length) {
            new Chart(typeChartCtx, {
                type: 'pie',
                data: {
                    labels: questionTypeData.labels,
                    datasets: [{
                        data: questionTypeData.data,
                        backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
                        hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#be2617'],
                        borderColor: '#fff',
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    legend: { position: 'bottom' },
                },
            });
        }

        const invitationUsageData = @json($invitationUsageChart);
        const usageChartCtx = document.getElementById('invitationUsageChart');
        if (usageChartCtx && invitationUsageData.labels.length) {
            new Chart(usageChartCtx, {
                type: 'bar',
                data: {
                    labels: invitationUsageData.labels,
                    datasets: [{
                        label: '{{ __('Respuestas') }}',
                        data: invitationUsageData.data,
                        backgroundColor: '#4e73df',
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                precision: 0,
                            },
                        }],
                    },
                    legend: { display: false },
                },
            });
        }
    });
</script>
@endpush

