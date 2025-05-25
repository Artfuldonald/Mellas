{{-- resources/views/components/text-input.blade.php --}}
@props([
    'disabled' => false,
    'type' => 'text' // Default type
])

<input
    type="{{ $type }}"
    {{ $disabled ? 'disabled' : '' }}
    {{--
        Removed: dark:border-gray-700, dark:bg-gray-900, dark:text-gray-300, dark:focus:border-indigo-600, dark:focus:ring-indigo-600
        Added: bg-white, text-gray-900 (or text-black if preferred)
    --}}
    {!! $attributes->merge(['class' => 'border-gray-300 bg-white text-gray-900 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm disabled:opacity-50']) !!}
>
