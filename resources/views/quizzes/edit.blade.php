<x-app-layout>
    <x-slot name="header">
        {{ __('Editar encuesta') }}: <span class="text-primary">{{ $quiz->title }}</span>
    </x-slot>

    <div class="row">
        <div class="col-lg-9">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    @php
                        $statusLabels = [
                            'draft' => __('Borrador'),
                            'published' => __('Publicada'),
                            'closed' => __('Cerrada'),
                        ];
                    @endphp
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                        <div class="mb-2 mb-md-0">
                            <h6 class="m-0 font-weight-bold text-primary">{{ __('Información general') }}</h6>
                            <span class="badge badge-pill badge-light text-secondary mt-1">
                                <i class="fas fa-clipboard-check mr-1"></i>{{ $statusLabels[$quiz->status] ?? ucfirst($quiz->status) }}
                            </span>
                        </div>
                        <div class="btn-group">
                            @if ($quiz->status === 'draft')
                                <form action="{{ route('quizzes.publish', $quiz) }}" method="POST" class="js-show-loader">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="fas fa-bullhorn mr-1"></i>{{ __('Publicar encuesta') }}
                                    </button>
                                </form>
                            @elseif ($quiz->status === 'published')
                                <form action="{{ route('quizzes.close', $quiz) }}" method="POST" class="js-show-loader" onsubmit="return confirm('{{ __('¿Cerrar la encuesta? Los estudiantes ya no podrán responder.') }}')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-warning">
                                        <i class="fas fa-lock mr-1"></i>{{ __('Cerrar encuesta') }}
                                    </button>
                                </form>
                            @else
                                <span class="text-muted small">{{ __('Encuesta cerrada. Próximamente podrás solicitar recomendaciones de IA.') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('quizzes.update', $quiz) }}">
                        @csrf
                        @method('PUT')

                        @include('quizzes._form')

                        <div class="d-flex justify-content-between">
                            <div>
                                <a href="{{ route('quizzes.index') }}" class="btn btn-light">
                                    {{ __('Regresar al listado') }}
                                </a>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-1"></i>{{ __('Guardar cambios') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="m-0 font-weight-bold text-primary">{{ __('Preguntas de la encuesta') }}</h6>
                        <small class="text-muted">{{ __('Ordena y personaliza las preguntas que verán tus estudiantes.') }}</small>
                    </div>
                    <div class="text-right">
                        <span class="badge badge-info badge-pill mr-2">{{ $quiz->questions->count() }}</span>
                        <a href="{{ route('quizzes.questions.create', $quiz) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-plus mr-1"></i>{{ __('Agregar pregunta') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if ($quiz->questions->count())
                        <div class="list-group">
                            @foreach ($quiz->questions as $question)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <span class="badge badge-light mr-2">#{{ $question->position }}</span>
                                            <strong>{{ $question->title }}</strong>
                                            <span class="badge badge-pill badge-secondary text-uppercase ml-2">
                                                @php
                                                    $typeLabels = [
                                                        'multiple_choice' => __('Opción múltiple'),
                                                        'multi_select' => __('Selección múltiple'),
                                                        'scale' => __('Escala'),
                                                        'open_text' => __('Respuesta abierta'),
                                                        'numeric' => __('Respuesta numérica'),
                                                    ];
                                                @endphp
                                                {{ $typeLabels[$question->type] ?? $question->type }}
                                            </span>
                                        </div>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('quizzes.questions.edit', [$quiz, $question]) }}" class="btn btn-outline-primary" title="{{ __('Editar pregunta') }}">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('quizzes.questions.destroy', [$quiz, $question]) }}" method="POST" onsubmit="return confirm('{{ __('¿Deseas eliminar esta pregunta?') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="{{ __('Eliminar') }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    @if ($question->description)
                                        <p class="small text-muted mb-0 mt-2">{{ $question->description }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-layer-group fa-2x mb-3"></i>
                            <p class="mb-0">{{ __('Aún no has agregado preguntas a esta encuesta.') }}</p>
                            <a href="{{ route('quizzes.questions.create', $quiz) }}" class="btn btn-sm btn-primary mt-3">
                                <i class="fas fa-plus mr-1"></i>{{ __('Crear primera pregunta') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('Códigos de invitación') }}</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('quizzes.invitations.store', $quiz) }}" class="mb-3">
                        @csrf
                        <div class="form-group">
                            <label class="font-weight-bold">{{ __('Nombre para identificar el código (opcional)') }}</label>
                            <input type="text" name="label" class="form-control form-control-sm @error('label') is-invalid @enderror" value="{{ old('label') }}">
                            @error('label')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="font-weight-bold">{{ __('Límite de usos') }}</label>
                                <input type="number" min="1" name="max_uses" class="form-control form-control-sm @error('max_uses') is-invalid @enderror" value="{{ old('max_uses') }}">
                                @error('max_uses')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label class="font-weight-bold">{{ __('Expira el') }}</label>
                                <input type="datetime-local" name="expires_at" class="form-control form-control-sm @error('expires_at') is-invalid @enderror" value="{{ old('expires_at') }}">
                                @error('expires_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="invitation-active" name="is_active" value="1" checked>
                                <label class="custom-control-label" for="invitation-active">{{ __('Activar inmediatamente') }}</label>
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus mr-1"></i>{{ __('Generar código') }}
                            </button>
                        </div>
                    </form>

                    @if ($quiz->invitations->count())
                        <ul class="list-group mb-3">
                            @foreach ($quiz->invitations as $invitation)
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong class="h6 mb-1">{{ $invitation->label ?? __('Código sin título') }}</strong>
                                            <div class="font-weight-bold text-primary d-flex align-items-center">
                                                <span class="mr-2">{{ $invitation->code }}</span>
                                                <button type="button" class="btn btn-sm btn-outline-secondary copy-code-btn" data-code="{{ $invitation->code }}" title="{{ __('Copiar código') }}">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                            <div class="small text-muted">
                                                {{ __('Generado') }}: {{ $invitation->created_at->format('d/m/Y H:i') }}
                                                <br>
                                                {{ __('Usos') }}: {{ $invitation->uses_count }}{{ $invitation->max_uses ? ' / '.$invitation->max_uses : '' }}
                                                @if ($invitation->expires_at)
                                                    <br>{{ __('Expira') }}: {{ $invitation->expires_at->format('d/m/Y H:i') }}
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="badge badge-pill {{ $invitation->is_valid ? 'badge-success' : 'badge-secondary' }}">
                                                {{ $invitation->is_valid ? __('Disponible') : __('Inactivo / vencido') }}
                                            </span>
                                            <div class="btn-group btn-group-sm d-block mt-2">
                                                <form action="{{ route('quizzes.invitations.update', [$quiz, $invitation]) }}" method="POST" class="mr-1 d-inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="label" value="{{ $invitation->label }}">
                                                    <input type="hidden" name="max_uses" value="{{ $invitation->max_uses }}">
                                                    <input type="hidden" name="expires_at" value="{{ optional($invitation->expires_at)->format('Y-m-d\TH:i') }}">
                                                    <input type="hidden" name="is_active" value="{{ $invitation->is_active ? 0 : 1 }}">
                                                    <button type="submit" class="btn btn-outline-{{ $invitation->is_active ? 'warning' : 'success' }}" title="{{ $invitation->is_active ? __('Desactivar código') : __('Activar código') }}">
                                                        <i class="fas fa-power-off"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('quizzes.invitations.destroy', [$quiz, $invitation]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('¿Eliminar definitivamente este código?') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger" title="{{ __('Eliminar código') }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted small mb-0">{{ __('Aún no se han generado códigos de invitación.') }}</p>
                    @endif
                    <small class="text-muted d-block">{{ __('Comparte estos códigos con tus estudiantes para que puedan acceder a la encuesta.') }}</small>
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-danger">{{ __('Eliminar encuesta') }}</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        {{ __('Esta acción moverá la encuesta a la papelera. Las respuestas existentes no se eliminarán de inmediato.') }}
                    </p>
                    <form action="{{ route('quizzes.destroy', $quiz) }}" method="POST" onsubmit="return confirm('{{ __('¿Deseas eliminar esta encuesta?') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-block">
                            <i class="fas fa-trash mr-1"></i>{{ __('Eliminar encuesta') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.copy-code-btn').forEach(function (button) {
            button.addEventListener('click', function () {
                const code = this.dataset.code;
                if (!code) {
                    return;
                }
                navigator.clipboard.writeText(code).then(() => {
                    this.classList.remove('btn-outline-secondary');
                    this.classList.add('btn-success');
                    this.innerHTML = '<i class="fas fa-check"></i>';
                    setTimeout(() => {
                        this.classList.add('btn-outline-secondary');
                        this.classList.remove('btn-success');
                        this.innerHTML = '<i class="fas fa-copy"></i>';
                    }, 1500);
                });
            });
        });
    });
</script>
@endpush

