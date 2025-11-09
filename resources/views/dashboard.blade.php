@php use App\Models\User; @endphp

<x-app-layout>
    <x-slot name="header">
        @if ($role === User::ROLE_ADMIN)
            {{ __('Panel de administración') }}
        @elseif ($role === User::ROLE_TEACHER)
            {{ __('Panel de docente') }}
        @else
            {{ __('Panel de estudiante') }}
        @endif
    </x-slot>

    @if ($role === User::ROLE_ADMIN)
        @include('dashboard.partials.admin', [
            'stats' => $stats,
            'roleCounts' => $roleCounts,
            'recentUsers' => $recentUsers,
            'charts' => $charts ?? [],
        ])
    @elseif ($role === User::ROLE_TEACHER)
        @include('dashboard.partials.teacher', [
            'stats' => $stats,
            'recentSurveys' => $recentSurveys,
            'recentResponses' => $recentResponses,
            'pendingAnalysis' => $pendingAnalysis,
            'charts' => $charts ?? [],
        ])
    @else
        @include('dashboard.partials.student', [
            'stats' => $stats,
            'recentAttempts' => $recentAttempts,
            'charts' => $charts ?? [],
        ])
    @endif
</x-app-layout>

@push('scripts')
    @if (! empty($charts))
        @foreach ($charts as $chartInstance)
            {!! $chartInstance->script() !!}
        @endforeach
    @endif
@endpush
