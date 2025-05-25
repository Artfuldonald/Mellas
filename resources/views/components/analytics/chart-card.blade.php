@props([
    'title',
    'subtitle' => null,
    'chartId', // Required ID for the chart canvas/div
    'exportable' => false, // Show export button?
    'exportAction' => '#', // URL or JS function call for export
    'extraInfoSlot' => null, // Slot for extra info like Desktop/Mobile counts
])

<div {{ $attributes->merge(['class' => 'bg-gray-800 shadow rounded-lg p-6']) }}>
    <div class="flex justify-between items-start mb-4"> {{-- Changed items-center to items-start --}}
        <div>
            <h3 class="text-lg font-semibold text-white">{{ $title }}</h3>
            @if($subtitle)
                <p class="text-sm text-gray-400">{{ $subtitle }}</p>
            @endif
        </div>
        <div class="flex items-center space-x-4">
            {{-- Render extra info if slot is provided --}}
            @if($extraInfoSlot)
                <div class="text-right text-sm font-semibold">
                    {{ $extraInfoSlot }}
                </div>
            @endif
            {{-- Export Button (using the desired style) --}}
            @if($exportable)
                <button onclick="{{ str_starts_with($exportAction, 'javascript:') ? $exportAction : "window.location.href='{$exportAction}'" }}"
                        class="text-gray-400 hover:text-white text-sm flex items-center px-3 py-1 rounded-md bg-gray-700/50 hover:bg-gray-600/50 transition duration-150">
                    <x-heroicon-o-arrow-down-tray class="w-4 h-4 inline-block mr-1"/> {{-- Example icon --}}
                    Export
                </button>
            @endif
        </div>
    </div>

    {{-- Main content area for the chart canvas/div --}}
    <div class="chart-container-placeholder"> {{-- Add specific class if needed --}}
        {{ $slot }}
    </div>

     {{-- Optional: Footer for labels --}}
     @isset($footerSlot)
        <div class="chart-footer mt-2 px-1">
            {{ $footerSlot }}
        </div>
     @endisset
</div>