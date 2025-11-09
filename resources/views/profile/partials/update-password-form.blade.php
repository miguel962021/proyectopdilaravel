<section>
    <h5 class="font-weight-bold text-primary mb-2">{{ __('Actualizar contraseña') }}</h5>
    <p class="text-muted small mb-4">{{ __('Cambia tu contraseña de acceso para mantener tu cuenta segura.') }}</p>

    <form method="POST" action="{{ route('password.update') }}" class="js-show-loader">
        @csrf
        @method('put')

        <div class="form-group">
            <label for="current_password" class="font-weight-semibold">{{ __('Contraseña actual') }}</label>
            <input id="current_password" name="current_password" type="password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" autocomplete="current-password">
            @error('current_password', 'updatePassword')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="password" class="font-weight-semibold">{{ __('Nueva contraseña') }}</label>
            <input id="password" name="password" type="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror" autocomplete="new-password">
            @error('password', 'updatePassword')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="password_confirmation" class="font-weight-semibold">{{ __('Confirmar contraseña') }}</label>
            <input id="password_confirmation" name="password_confirmation" type="password" class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror" autocomplete="new-password">
            @error('password_confirmation', 'updatePassword')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex align-items-center">
            <button type="submit" class="btn btn-primary mr-3">{{ __('Guardar contraseña') }}</button>
            @if (session('status') === 'password-updated')
                <span class="text-success small">{{ __('Contraseña actualizada correctamente.') }}</span>
            @endif
        </div>
    </form>
</section>
