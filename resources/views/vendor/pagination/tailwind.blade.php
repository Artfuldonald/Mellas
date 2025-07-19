@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex flex-col sm:flex-row items-center justify-between space-y-4 sm:space-y-0">
        
        {{-- "Showing results" text - NOW VISIBLE ON ALL SCREENS --}}
        <div class="flex-1 flex items-center justify-start">
            <p class="text-sm text-gray-700 leading-5">
                {!! __('Showing') !!}
                @if ($paginator->firstItem())
                    <span class="font-semibold">{{ $paginator->firstItem() }}</span>
                    {!! __('to') !!}
                    <span class="font-semibold">{{ $paginator->lastItem() }}</span>
                @else
                    {{ $paginator->count() }}
                @endif
                {!! __('of') !!}
                <span class="font-semibold">{{ $paginator->total() }}</span>
                {!! __('results') !!}
            </p>
        </div>

        {{-- Numbered links container - NOW CENTERED --}}
        <div class="flex-1 flex justify-center sm:justify-end">
            <span class="relative z-0 inline-flex shadow-sm rounded-md">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <span aria-disabled="true" class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-400 bg-white border border-gray-300 cursor-default rounded-l-md">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50 focus:z-10 focus:outline-none focus:ring-1 focus:ring-pink-500 focus:border-pink-500 transition">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                    </a>
                @endif

                {{-- Pagination Elements (The Numbers) --}}
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span aria-disabled="true" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 cursor-default">{{ $element }}</span>
                    @endif
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-semibold text-white bg-pink-600 border border-pink-600 cursor-default z-10">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-600 bg-white border border-gray-300 hover:bg-gray-50 focus:z-10 focus:outline-none focus:ring-1 focus:ring-pink-500 focus:border-pink-500 transition">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50 focus:z-10 focus:outline-none focus:ring-1 focus:ring-pink-500 focus:border-pink-500 transition">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                    </a>
                @else
                    <span aria-disabled="true" class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-400 bg-white border border-gray-300 cursor-default rounded-r-md">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                    </span>
                @endif
            </span>
        </div>
    </nav>
@endif