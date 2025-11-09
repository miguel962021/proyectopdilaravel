<section>
    <h5 class="font-weight-bold text-primary mb-2">{{ __('Información personal') }}</h5>
    <p class="text-muted small mb-4">{{ __('Actualiza tu nombre y correo electrónico de contacto.') }}</p>

    <form id="send-verification" method="POST" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="POST" action="{{ route('profile.update') }}" class="js-show-loader">
        @csrf
        @method('patch')

        <div class="form-group">
            <label for="name" class="font-weight-semibold">{{ __('Nombre') }}</label>
            <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autocomplete="name">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="email" class="font-weight-semibold">{{ __('Correo electrónico') }}</label>
            <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required autocomplete="username">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="alert alert-warning small mt-3" role="alert">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>{{ __('Tu correo aún no ha sido verificado.') }}</span>
                        <button form="send-verification" class="btn btn-sm btn-primary">{{ __('Reenviar verificación') }}</button>
                    </div>

                    @if (session('status') === 'verification-link-sent')
                        <div class="mt-2 text-success">{{ __('Se envió un nuevo enlace de verificación a tu correo.') }}</div>
                    @endif
                </div>
            @endif
        </div>

        <div class="d-flex align-items-center">
            <button type="submit" class="btn btn-primary mr-3">{{ __('Guardar cambios') }}</button>
            @if (session('status') === 'profile-updated')
                <span class="text-success small">{{ __('Datos guardados correctamente.') }}</span>
            @endif
        </div>
    </form>
</section>
