@props([
    'name', // Unique name to identify the modal
    'show' => false, // Whether to show initially (usually false, controlled by JS)
    'maxWidth' => '2xl', // Controls the width class
    'focusable' => false // If true, focuses the first element on open
])

@php
// Tailwind classes for different max widths
$maxWidth = [
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
][$maxWidth ?? '2xl']; // Use provided or default to '2xl'
@endphp

{{--
    Alpine.js component definition:
    - `show`: Controls visibility, initialized by the $show prop.
    - focusable methods: Helpers to manage focus trapping within the modal.
    - x-init: Runs when Alpine initializes, sets up watcher for 'show'.
    - x-on:event: Listens for browser/Alpine events to control the modal.
    - x-show: Ties the element's display to the Alpine 'show' property.
    - `:class`: Can be used for dynamic classes (not used here but possible).
--}}
<div
    x-data="{
        show: @js($show),
        focusables() {
            let selector = 'a[href], button, input:not([type=\'hidden\']), textarea, select, details, [tabindex]:not([tabindex=\'-1\'])'
            return [...$el.querySelectorAll(selector)]
                .filter(el => !el.hasAttribute('disabled') && !el.closest('[x-show=\"false\"]'))
        },
        firstFocusable() { return this.focusables()[0] },
        lastFocusable() { return this.focusables().slice(-1)[0] },
        nextFocusable() { return this.focusables()[this.nextFocusableIndex()] || this.firstFocusable() },
        prevFocusable() { return this.focusables()[this.prevFocusableIndex()] || this.lastFocusable() },
        nextFocusableIndex() { return (this.focusables().indexOf(document.activeElement) + 1) % (this.focusables().length + 1) },
        prevFocusableIndex() { return Math.max(0, this.focusables().indexOf(document.activeElement)) -1 },
    }"
    x-init="$watch('show', value => {
        if (value) {
            document.body.classList.add('overflow-y-hidden');
            @if ($focusable) setTimeout(() => firstFocusable().focus(), 100); @endif
        } else {
            document.body.classList.remove('overflow-y-hidden');
        }
    })"
    x-on:open-modal.window="$event.detail == '{{ $name }}' ? show = true : null"
    x-on:close.stop="show = false"
    x-on:keydown.escape.window="show = false"
    x-on:keydown.tab.prevent="$event.shiftKey || nextFocusable().focus()"
    x-on:keydown.shift.tab.prevent="prevFocusable().focus()"
    x-show="show"
    class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50"
    style="display: {{ $show ? 'block' : 'none' }};" {{-- Set initial display state for non-JS users --}}
>
    {{-- Background Overlay --}}
    <div
        x-show="show"
        class="fixed inset-0 transform transition-all"
        x-on:click="show = false" {{-- Close modal when clicking overlay --}}
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="absolute inset-0 bg-gray-500 dark:bg-gray-900 opacity-75"></div>
    </div>

    {{-- Modal Content Panel --}}
    <div
        x-show="show"
        class="mb-6 bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full {{ $maxWidth }} sm:mx-auto"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
    >
        {{-- Content passed into the component tag goes here --}}
        {{ $slot }}
    </div>
</div>