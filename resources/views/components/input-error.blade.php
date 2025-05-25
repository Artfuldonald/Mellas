@props(['messages'])

@if ($messages)
    {{-- The $attributes parameter allows you to pass additional classes, like 'mt-2' --}}
    <ul {{ $attributes->merge(['class' => 'text-sm text-red-600 dark:text-red-400 space-y-1']) }}>
        {{-- Loop through the error messages passed from the controller --}}
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif