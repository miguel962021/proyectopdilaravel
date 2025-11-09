<section>
    <h5 class="font-weight-bold text-danger mb-2">{{ __('Eliminar cuenta') }}</h5>
    <p class="text-muted small mb-4">{{ __('Al eliminar tu cuenta se borrarán de forma permanente tus encuestas y respuestas asociadas. Descarga cualquier información importante antes de continuar.') }}</p>

    <button type="button" class="btn btn-outline-danger" data-toggle="modal" data-target="#confirmDeleteAccount">{{ __('Eliminar mi cuenta') }}</button>

    <div class="modal fade" id="confirmDeleteAccount" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteAccountLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteAccountLabel">{{ __('Confirmar eliminación de cuenta') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" action="{{ route('profile.destroy') }}" class="js-show-loader">
                    @csrf
                    @method('delete')
                    <div class="modal-body">
                        <p class="text-muted small">
                            {{ __('¿Seguro que deseas eliminar tu cuenta? Esta acción no podrá deshacerse.') }}
                        </p>
                        <div class="form-group mb-0">
                            <label for="delete_password" class="font-weight-semibold">{{ __('Ingresa tu contraseña para confirmar') }}</label>
                            <input id="delete_password" name="password" type="password" class="form-control @error('password', 'userDeletion') is-invalid @enderror" placeholder="********" required>
                            @error('password', 'userDeletion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancelar') }}</button>
                        <button type="submit" class="btn btn-danger">{{ __('Eliminar cuenta') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
