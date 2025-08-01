@props([
    'items' => []
])

@if (!empty($items))
<nav aria-label="Breadcrumb">
    {{-- This is the container that makes it scrollable on mobile --}}
    <div class="hide-scrollbar flex items-center overflow-x-auto whitespace-nowrap text-sm text-gray-500">
        
        {{-- Always add a Home link at the start --}}
        <div class="flex items-center">
            <a href="{{ route('home') }}" class="hover:text-pink-600">Home</a>
        </div>

        {{-- Loop through the breadcrumb items passed from the controller --}}
        @foreach ($items as $item)
            <div class="flex items-center">
                <x-heroicon-s-chevron-right class="w-3 h-3 mx-1.5 text-gray-400 flex-shrink-0"/>
                <a href="{{ $item['url'] }}" 
                   class="hover:text-pink-600 truncate @if($loop->last) text-gray-800 font-medium @endif"
                   @if($loop->last) aria-current="page" @endif>
                    {{ $item['name'] }}
                </a>
            </div>
        @endforeach
    </div>
</nav>
@endif