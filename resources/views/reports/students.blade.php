<x-app-layout>
    <x-slot name="header">
        {{ __('Reporte de estudiantes') }}
    </x-slot>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('Participación por estudiante') }}</h6>
            <span class="badge badge-light text-muted text-uppercase">{{ __('En desarrollo') }}</span>
        </div>
        <div class="card-body">
            <p class="text-muted small">
                {{ __('Aquí podrás analizar la participación de los estudiantes, su progreso y resultados comparativos entre encuestas.') }}
            </p>
            <ul class="text-muted small mb-0">
                <li>{{ __('Resumen de encuestas completadas vs. pendientes.') }}</li>
                <li>{{ __('Detección de estudiantes con baja participación.') }}</li>
                <li>{{ __('Descarga de reportes por cohorte o grupo.') }}</li>
            </ul>
        </div>
    </div>
</x-app-layout>
