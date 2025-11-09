<x-app-layout>
    <x-slot name="header">
        {{ __('Reporte general') }}
    </x-slot>

    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">{{ __('Encuestas activas') }}</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">—</div>
                        </div>
                        <i class="fas fa-bullhorn fa-2x text-primary"></i>
                    </div>
                    <p class="text-muted small mb-0 mt-3">{{ __('Próximamente verás métricas globales consolidadas de la plataforma.') }}</p>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">{{ __('Participación estudiantil') }}</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">—</div>
                        </div>
                        <i class="fas fa-user-graduate fa-2x text-success"></i>
                    </div>
                    <p class="text-muted small mb-0 mt-3">{{ __('Integraremos tablas comparativas por curso y período académico.') }}</p>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">{{ __('Análisis IA generados') }}</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">—</div>
                        </div>
                        <i class="fas fa-robot fa-2x text-info"></i>
                    </div>
                    <p class="text-muted small mb-0 mt-3">{{ __('Aquí se mostrarán insights agregados derivados de los informes detallados.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('Resumen ejecutivo') }}</h6>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-0">{{ __('Estamos preparando esta sección para consolidar indicadores clave y permitir exportes rápidos del estado general.') }}</p>
        </div>
    </div>
</x-app-layout>
