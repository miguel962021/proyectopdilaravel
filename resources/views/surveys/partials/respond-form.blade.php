<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-start mb-4">
                    <div class="mb-3 mb-lg-0">
                        <h1 class="h4 text-gray-900 mb-1">{{ $quiz->title }}</h1>
                        @if ($quiz->description)
                            <p class="text-muted small mb-0">{{ $quiz->description }}</p>
                        @endif
                    </div>
                    <span class="badge badge-pill badge-primary text-uppercase">
                        {{ __('Código:') }} {{ $invitation->code }}
                    </span>
                </div>

                @if ($errors->has('general'))
                    <div class="alert alert-danger small">
                        {{ $errors->first('general') }}
                    </div>
                @endif

                @if (session('status'))
                    <div class="alert alert-success small">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('surveys.respond.submit', $invitation->code) }}" class="js-show-loader">
                    @csrf

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="small text-muted" for="participant_name">{{ __('Nombre (opcional)') }}</label>
                            <input type="text" id="participant_name" name="participant_name" class="form-control form-control-user" value="{{ old('participant_name', auth()->user()->name ?? '') }}" placeholder="{{ __('Tu nombre completo') }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label class="small text-muted" for="participant_email">{{ __('Correo electrónico (opcional)') }}</label>
                            <input type="email" id="participant_email" name="participant_email" class="form-control form-control-user @error('participant_email') is-invalid @enderror" value="{{ old('participant_email', auth()->user()->email ?? '') }}" placeholder="{{ __('correo@ejemplo.com') }}">
                            @error('participant_email')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <hr class="sidebar-divider">

                    @foreach ($questions as $index => $question)
                        <div class="mb-5">
                            <h5 class="font-weight-bold text-primary">
                                {{ ($index + 1).'. '.$question->title }}
                                <span class="text-danger">*</span>
                            </h5>

                            @if ($question->description)
                                <p class="text-muted small mb-3">{{ $question->description }}</p>
                            @endif

                            @php
                                $fieldName = "responses.{$question->id}";
                                $fieldError = $errors->first($fieldName) ?: $errors->first("{$fieldName}.*");
                            @endphp

                            @switch($question->type)
                                @case('multiple_choice')
                                    @foreach ($question->options as $option)
                                        <div class="custom-control custom-radio mb-2">
                                            <input class="custom-control-input @if($fieldError) is-invalid @endif" type="radio" id="question-{{ $question->id }}-option-{{ $option->id }}" name="responses[{{ $question->id }}]" value="{{ $option->id }}" {{ (string) $option->id === (string) old("responses.{$question->id}") ? 'checked' : '' }} required>
                                            <label class="custom-control-label" for="question-{{ $question->id }}-option-{{ $option->id }}">
                                                {{ $option->label }}
                                            </label>
                                        </div>
                                    @endforeach
                                    @break

                                @case('multi_select')
                                    @php
                                        $selected = collect(old("responses.{$question->id}", []))->map(fn ($value) => (int) $value)->all();
                                    @endphp
                                    @foreach ($question->options as $option)
                                        <div class="custom-control custom-checkbox mb-2">
                                            <input class="custom-control-input @if($fieldError) is-invalid @endif" type="checkbox" id="question-{{ $question->id }}-option-{{ $option->id }}" name="responses[{{ $question->id }}][]" value="{{ $option->id }}" {{ in_array($option->id, $selected, true) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="question-{{ $question->id }}-option-{{ $option->id }}">
                                                {{ $option->label }}
                                            </label>
                                        </div>
                                    @endforeach
                                    @break

                                @case('scale')
                                    @php
                                        $min = data_get($question->settings, 'scale_min', 1);
                                        $max = data_get($question->settings, 'scale_max', 5);
                                        $step = max(1, data_get($question->settings, 'scale_step', 1));
                                        $current = old("responses.{$question->id}");
                                    @endphp
                                    <div class="form-group">
                                        <select class="custom-select @if($fieldError) is-invalid @endif" name="responses[{{ $question->id }}]" required>
                                            <option value="">{{ __('Selecciona una opción') }}</option>
                                            @for ($value = $min; $value <= $max; $value += $step)
                                                <option value="{{ $value }}" {{ (string) $value === (string) $current ? 'selected' : '' }}>
                                                    {{ $value }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                    @break

                                @case('open_text')
                                    <div class="form-group">
                                        <textarea class="form-control @if($fieldError) is-invalid @endif" name="responses[{{ $question->id }}]" rows="4" required>{{ old("responses.{$question->id}") }}</textarea>
                                    </div>
                                    @break

                                @case('numeric')
                                    <div class="form-group">
                                        <input type="number" step="any" class="form-control @if($fieldError) is-invalid @endif" name="responses[{{ $question->id }}]" value="{{ old("responses.{$question->id}") }}" required>
                                    </div>
                                    @break

                                @default
                                    <p class="text-muted small">{{ __('Tipo de pregunta no soportado actualmente.') }}</p>
                            @endswitch

                            @if ($fieldError)
                                <span class="text-danger small d-block mt-2">{{ $fieldError }}</span>
                            @endif
                        </div>
                    @endforeach

                    <button type="submit" class="btn btn-primary btn-user btn-block">
                        {{ __('Enviar respuestas') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
