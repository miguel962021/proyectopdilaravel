<x-app-layout>
    <x-slot name="header">
        {{ __('Reporte de encuestas') }}
    </x-slot>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('Clasificación de encuestas') }}</h6>
        </div>
        <div class="card-body">
            <p class="text-muted small">
                {{ __('Muy pronto podrás filtrar y comparar las encuestas por estado, fecha de publicación, área académica y nivel de satisfacción.') }}
            </p>
            <div class="alert alert-info small mb-0" role="alert">
                <i class="fas fa-info-circle mr-1"></i>
                {{ __('Esta sección permitirá exportar listados personalizados y comparar tendencias históricas.') }}
            </div>
        </div>
    </div>
</x-app-layout>
