<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">{{ __('Encuestas disponibles') }}</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['available_surveys'] ?? 0) }}</div>
                    </div>
                    <span class="text-primary"><i class="fas fa-clipboard-list fa-2x"></i></span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">{{ __('Encuestas completadas') }}</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['completed_surveys'] ?? 0) }}</div>
                    </div>
                    <span class="text-success"><i class="fas fa-check-circle fa-2x"></i></span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">{{ __('Última actividad') }}</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ optional($stats['last_activity'])->diffForHumans() ?? __('Sin registros') }}
                        </div>
                    </div>
                    <span class="text-info"><i class="fas fa-history fa-2x"></i></span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">{{ __('Mi actividad semanal') }}</h6>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    @if (! empty($charts['usage']))
                        {!! $charts['usage']->container() !!}
                    @else
                        <p class="text-muted small mb-0">{{ __('Aún no registras actividad en los últimos días.') }}</p>
                    @endif
                </div>
                <p class="text-muted small mt-3 mb-0">{{ __('Cantidad de encuestas que enviaste cada día durante la última semana.') }}</p>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">{{ __('Sugerencias') }}</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled text-muted small mb-0">
                    <li class="mb-3"><i class="fas fa-qrcode text-primary mr-2"></i>{{ __('Accede con tus códigos a tiempo para no perder encuestas activas.') }}</li>
                    <li class="mb-3"><i class="fas fa-comment-dots text-success mr-2"></i>{{ __('Usa los comentarios abiertos para compartir sugerencias con tus docentes.') }}</li>
                    <li class="mb-0"><i class="fas fa-chart-bar text-info mr-2"></i>{{ __('Revisa los resultados cuando estén disponibles para conocer la retroalimentación del curso.') }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">{{ __('Encuestas que completé recientemente') }}</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ __('Encuesta') }}</th>
                                <th class="text-right">{{ __('Fecha de envío') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentAttempts as $attempt)
                                <tr>
                                    <td>{{ $attempt->quiz?->title ?? __('Encuesta eliminada') }}</td>
                                    <td class="text-right text-muted small">{{ optional($attempt->completed_at ?? $attempt->created_at)->format('d/m/Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-4">{{ __('Aún no has completado encuestas.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
