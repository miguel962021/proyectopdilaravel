<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            {{ __('Usuarios registrados') }}</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['users'] ?? 0) }}</div>
                    </div>
                    <div class="text-primary">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            {{ __('Encuestas totales') }}</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['surveys'] ?? 0) }}</div>
                    </div>
                    <div class="text-success">
                        <i class="fas fa-poll-h fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            {{ __('Respuestas recibidas') }}</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['attempts'] ?? 0) }}</div>
                    </div>
                    <div class="text-info">
                        <i class="fas fa-chart-line fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            {{ __('Invitaciones activas') }}</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['invites'] ?? 0) }}</div>
                    </div>
                    <div class="text-warning">
                        <i class="fas fa-key fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">{{ __('Actividad semanal de respuestas') }}</h6>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    @if (! empty($charts['usage']))
                        {!! $charts['usage']->container() !!}
                    @else
                        <p class="text-muted small mb-0">{{ __('Aún no hay datos suficientes para este gráfico.') }}</p>
                    @endif
                </div>
                <p class="text-muted small mt-3 mb-0">{{ __('Muestra cuántas respuestas se han registrado día a día durante la última semana en toda la plataforma.') }}</p>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">{{ __('Distribución por rol') }}</h6>
            </div>
            <div class="card-body">
                <div class="chart-pie pt-4 pb-2">
                    @if (! empty($charts['roles']))
                        {!! $charts['roles']->container() !!}
                    @else
                        <p class="text-muted small mb-0">{{ __('Sin datos para mostrar la distribución de roles.') }}</p>
                    @endif
                </div>
                <div class="mt-4 text-center small">
                    <span class="mr-2"><i class="fas fa-circle text-primary"></i> {{ __('Administradores') }}</span>
                    <span class="mr-2"><i class="fas fa-circle text-success"></i> {{ __('Docentes') }}</span>
                    <span class="mr-2"><i class="fas fa-circle text-info"></i> {{ __('Estudiantes') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-4 col-lg-5 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">{{ __('Fuentes de participación') }}</h6>
            </div>
            <div class="card-body">
                <div class="chart-pie pt-4 pb-2">
                    @if (! empty($charts['participation']))
                        {!! $charts['participation']->container() !!}
                    @else
                        <p class="text-muted small mb-0">{{ __('Sin datos disponibles para las fuentes de participación.') }}</p>
                    @endif
                </div>
                <div class="mt-4 text-center small">
                    <span class="mr-2"><i class="fas fa-circle text-primary"></i> {{ __('Invitaciones directas') }}</span>
                    <span class="mr-2"><i class="fas fa-circle text-success"></i> {{ __('Docentes compartieron') }}</span>
                    <span class="mr-2"><i class="fas fa-circle text-info"></i> {{ __('Accesos externos') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-8 col-lg-7 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">{{ __('Progreso de iniciativas') }}</h6>
            </div>
            <div class="card-body">
                <h4 class="small font-weight-bold">{{ __('Migración de cursos') }} <span class="float-right">40%</span></h4>
                <div class="progress mb-4">
                    <div class="progress-bar bg-danger" role="progressbar" style="width: 40%" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100"></div>
                </div>

                <h4 class="small font-weight-bold">{{ __('Lanzamiento de encuestas piloto') }} <span class="float-right">60%</span></h4>
                <div class="progress mb-4">
                    <div class="progress-bar bg-warning" role="progressbar" style="width: 60%" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"></div>
                </div>

                <h4 class="small font-weight-bold">{{ __('Integración de IA en reportes') }} <span class="float-right">75%</span></h4>
                <div class="progress mb-4">
                    <div class="progress-bar" role="progressbar" style="width: 75%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                </div>

                <h4 class="small font-weight-bold">{{ __('Capacitación docente') }} <span class="float-right">Completo</span></h4>
                <div class="progress">
                    <div class="progress-bar bg-success" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">{{ __('Usuarios recientes') }}</h6>
                <span class="badge badge-light text-muted text-uppercase">{{ __('Últimos 5 registros') }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0 table-hover table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ __('Nombre') }}</th>
                                <th>{{ __('Correo') }}</th>
                                <th class="text-right">{{ __('Rol') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentUsers as $recentUser)
                                <tr>
                                    <td>{{ $recentUser->name }}</td>
                                    <td class="text-muted small">{{ $recentUser->email }}</td>
                                    <td class="text-right">
                                        <span class="badge badge-pill badge-secondary text-uppercase">{{ __($recentUser->role) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">{{ __('Todavía no hay usuarios registrados.') }}</td>
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
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">{{ __('Resumen general') }}</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-4">
                    {{ __('Monitorea la adopción del sistema. Desde aquí puedes identificar picos de participación y asegurar que docentes y estudiantes estén utilizando las encuestas.') }}
                </p>
                <ul class="list-unstyled mb-0 text-muted small">
                    <li class="mb-3"><i class="fas fa-check text-success mr-2"></i>{{ __('Verifica que existan suficientes docentes creando encuestas para alcanzar a los estudiantes.') }}</li>
                    <li class="mb-3"><i class="fas fa-chart-bar text-primary mr-2"></i>{{ __('Revisa la actividad semanal para detectar periodos de baja participación y programar acompañamiento.') }}</li>
                    <li class="mb-0"><i class="fas fa-lightbulb text-warning mr-2"></i>{{ __('Recuerda generar reportes consolidados para las autoridades académicas.') }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>
