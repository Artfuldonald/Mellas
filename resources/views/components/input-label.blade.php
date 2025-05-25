@props(['value', 'for'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-gray-900']) }} @isset($for) for="{{ $for }}" @endisset>
    {{-- Removed dark:text-gray-300, kept text-gray-900 --}}
    {{ $value ?? $slot }}
</label>