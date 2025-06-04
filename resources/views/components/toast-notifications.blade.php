{{-- resources/views/components/toast-notifications.blade.php --}}
<div
    x-data="toastHandler()"
    @toast-show.window="showToast($event.detail)"
    class="fixed top-5 right-5 z-[100] w-full max-w-xs space-y-3"
    role="status"
    aria-live="polite"
>
    {{-- Toasts will be dynamically added here by Alpine --}}
    <template x-for="(toast, index) in toasts" :key="toast.id">
        <div
            x-show="toast.visible"
            x-transition:enter="transform ease-out duration-300 transition"
            x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @mouseover="toast.hovered = true"
            @mouseleave="toast.hovered = false; restartTimeout(toast)"
            class="max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden"
        >
            <div class="p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        {{-- Success Icon --}}
                        <template x-if="toast.type === 'success'">
                            <x-heroicon-o-check-circle class="h-6 w-6 text-green-500" />
                        </template>
                        {{-- Error Icon --}}
                        <template x-if="toast.type === 'error'">
                            <x-heroicon-o-x-circle class="h-6 w-6 text-red-500" />
                        </template>
                        {{-- Info Icon --}}
                        <template x-if="toast.type === 'info'">
                            <x-heroicon-o-information-circle class="h-6 w-6 text-blue-500" />
                        </template>
                        {{-- Warning Icon --}}
                        <template x-if="toast.type === 'warning'">
                            <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-yellow-500" />
                        </template>
                    </div>
                    <div class="ml-3 w-0 flex-1 pt-0.5">
                        <p class="text-sm font-medium text-gray-900" x-text="toast.title || capitalizeFirst(toast.type)"></p>
                        <p class="mt-1 text-sm text-gray-600" x-html="toast.message"></p> {{-- Use x-html if message can contain simple HTML like bold --}}
                    </div>
                    <div class="ml-4 flex-shrink-0 flex">
                        <button @click="removeToast(toast.id)" type="button" class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500">
                            <span class="sr-only">Close</span>
                            <x-heroicon-s-x-mark class="h-5 w-5" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>