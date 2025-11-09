<x-app-layout>
    <x-slot name="header">
        {{ __('Informe detallado de IA') }}
    </x-slot>

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">{{ $quiz->title }}</h1>
            <p class="text-muted small mb-0">{{ __('Análisis generado automáticamente a partir de las respuestas registradas.') }}</p>
        </div>
        <div class="d-flex flex-wrap">
            <a href="{{ route('quizzes.show', $quiz) }}" class="btn btn-outline-secondary btn-sm mr-2 mb-2">
                <i class="fas fa-arrow-left mr-1"></i>{{ __('Volver a la encuesta') }}
            </a>
            <a href="{{ route('quizzes.analysis.export', $quiz) }}" class="btn btn-primary btn-sm mb-2" target="_blank">
                <i class="fas fa-file-download mr-1"></i>{{ __('Exportar informe') }}
            </a>
        </div>
    </div>

    @if ($analysis)
        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">{{ __('Resumen ejecutivo') }}</h6>
                        <span class="badge badge-success text-uppercase">{{ __('Completado') }}</span>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-2">
                            <i class="fas fa-clock mr-1"></i>{{ __('Generado el :date', ['date' => optional($analysisSummary['completed_at'])->format('d/m/Y H:i') ?? '—']) }}
                        </p>
                        <p class="mb-0">{{ $analysisSummary['summary'] ?? __('El motor de IA no devolvió un resumen.') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">{{ __('Indicadores clave') }}</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0 text-muted small">
                            <li class="mb-2"><i class="fas fa-list-ol text-primary mr-2"></i>{{ __('Preguntas: :count', ['count' => $quiz->questions->count()]) }}</li>
                            <li class="mb-2"><i class="fas fa-users text-success mr-2"></i>{{ __('Respuestas: :count', ['count' => $quiz->attempts->count()]) }}</li>
                            <li class="mb-0"><i class="fas fa-robot text-info mr-2"></i>{{ __('Análisis IA más reciente disponible para exportación.') }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        @if (! empty($analysisCharts))
            <div class="row">
                @foreach ($analysisCharts as $chartEntry)
                    @php
                        /** @var \ArielMejiaDev\LarapexCharts\LarapexChart $chart */
                        $chart = $chartEntry['chart'];
                    @endphp
                    <div class="col-xl-6 col-lg-6 mb-4">
                        <div class="card shadow h-100">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-primary">{{ $chartEntry['question'] }}</h6>
                            </div>
                            <div class="card-body">
                                {!! $chart->container() !!}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">{{ __('Hallazgos cuantitativos') }}</h6>
                    </div>
                    <div class="card-body">
                        @forelse ($quantitativeInsights as $insight)
                            <div class="mb-3">
                                <h6 class="text-gray-800 font-weight-bold mb-1">{{ $insight['question'] }}</h6>
                                <ul class="text-muted small mb-0">
                                    <li>{{ __('Respuestas registradas: :count', ['count' => $insight['total_responses'] ?? 0]) }}</li>
                                    @if (! empty($insight['average']))
                                        <li>{{ __('Promedio: :value', ['value' => $insight['average']]) }}</li>
                                    @endif
                                    @if (! empty($insight['options']))
                                        @foreach ($insight['options'] as $option)
                                            <li>{{ $option['label'] }}: {{ $option['count'] }} ({{ $option['percentage'] ?? $option['count'] }}{{ isset($option['percentage']) ? '%' : '' }})</li>
                                        @endforeach
                                    @endif
                                </ul>
                            </div>
                        @empty
                            <p class="text-muted small mb-0">{{ __('No hay datos cuantitativos disponibles.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">{{ __('Temas cualitativos') }}</h6>
                    </div>
                    <div class="card-body">
                        @forelse ($analysisSummary['qualitative'] ?? [] as $theme)
                            <div class="mb-3">
                                <h6 class="text-gray-800 font-weight-bold mb-1">{{ $theme['theme'] ?? $theme['question'] ?? __('Tema') }}</h6>
                                @if (! empty($theme['evidence']))
                                    <ul class="list-unstyled text-muted small mb-0">
                                        @foreach ($theme['evidence'] as $quote)
                                            <li class="mb-1"><i class="fas fa-quote-left mr-1 text-info"></i>{{ $quote }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-muted small mb-0">{{ __('Sin testimonios destacados.') }}</p>
                                @endif
                            </div>
                        @empty
                            <p class="text-muted small mb-0">{{ __('Aún no existen temas cualitativos destacados.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">{{ __('Recomendaciones de IA') }}</h6>
                <span class="badge badge-light text-muted text-uppercase">{{ __('Acciones sugeridas') }}</span>
            </div>
            <div class="card-body">
                @if (! empty($analysisSummary['recommendations']))
                    <ul class="text-muted small mb-0">
                        @foreach ($analysisSummary['recommendations'] as $recommendation)
                            <li class="mb-2">{{ $recommendation }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted small mb-0">{{ __('La IA no generó recomendaciones para esta encuesta. Puedes regenerar el informe desde la vista principal de la encuesta.') }}</p>
                @endif
            </div>
        </div>
    @else
        <div class="alert alert-info" role="alert">
            <i class="fas fa-info-circle mr-2"></i>{{ __('Todavía no se ha generado un informe de IA para esta encuesta. Cierra la encuesta o solicita el análisis desde la pantalla principal.') }}
        </div>
    @endif
</x-app-layout>

@push('scripts')
    @if (! empty($analysisCharts))
        @foreach ($analysisCharts as $chartEntry)
            {!! $chartEntry['chart']->script() !!}
        @endforeach
    @endif
@endpush
