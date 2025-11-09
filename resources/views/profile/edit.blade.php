<x-app-layout>
    <x-slot name="header">
        {{ __('Mi perfil') }}
    </x-slot>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('Datos personales') }}</h6>
                </div>
                <div class="card-body">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('Seguridad de la cuenta') }}</h6>
                </div>
                <div class="card-body">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-danger">{{ __('Acciones avanzadas') }}</h6>
        </div>
        <div class="card-body">
            @include('profile.partials.delete-user-form')
        </div>
    </div>

    @push('scripts')
        @if ($errors->userDeletion->isNotEmpty())
            <script>
                $('#confirmDeleteAccount').modal('show');
            </script>
        @endif
    @endpush
</x-app-layout>
