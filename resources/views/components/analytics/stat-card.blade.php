@props([
    'title',
    'value',
    'change' => null, // Optional percentage change
    'changeLabel' => 'from last month', // Default label
    'icon' => null, // Optional Heroicon name (short form like 'currency-dollar')
    'iconBgColor' => 'bg-indigo-500', // Default icon background
    'ctaLink' => null, // Optional link for a button
    'ctaText' => null, // Optional text for the button
    'showChartPlaceholder' => false, // Option to show mini chart placeholder
])

<div {{ $attributes->merge(['class' => 'bg-gray-800 shadow rounded-lg p-5']) }}> {{-- Adjusted padding --}}
    <div class="flex justify-between items-start mb-3"> {{-- Adjusted margin --}}
        <div>
            <h3 class="text-base font-medium text-gray-800">{{ $title }}</h3>
            <p class="text-3xl font-bold text-gray-500 mt-1">{{ $value }}</p>
            @if(!is_null($change))
                <p @class(['text-xs mt-1', 'text-green-400' => $change >= 0, 'text-red-400' => $change < 0])>
                    {{ $change >= 0 ? '↑' : '↓' }} {{ number_format(abs($change), 1) }}% {{ $changeLabel }}
                </p>
            @endif
        </div>
        {{-- Optional Icon --}}
        @if($icon)
            <div @class(['p-2 rounded-md flex items-center justify-center', $iconBgColor])>
                <x-dynamic-component :component="'heroicon-o-'.$icon" class="h-5 w-5 text-white"/>
            </div>
        @endif
        {{-- Optional Mini Chart Placeholder --}}
        @if($showChartPlaceholder)
             <div class="mini-chart-placeholder mt-1 self-end"></div>
        @endif
    </div>

    {{-- Optional CTA Button --}}
    @if($ctaLink && $ctaText)
        <div class="mt-4">
            <a href="{{ $ctaLink }}" class="text-sm font-medium text-white bg-pink-900/50 px-3 py-1 rounded-md transition duration-150">
                {{ $ctaText }}
            </a>
        </div>
    @endif
</div>