@php
    $formData = compact('quiz', 'invitation', 'questions');
@endphp

@auth
    <x-app-layout>
        <x-slot name="header">
            {{ $quiz->title }}
        </x-slot>

        @include('surveys.partials.respond-form', $formData)
    </x-app-layout>
@else
    <x-guest-layout>
        @include('surveys.partials.respond-form', $formData)
    </x-guest-layout>
@endauth


