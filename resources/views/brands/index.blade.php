<x-app-layout title="Shop by Brand">
    <div class="bg-pink-50 py-8 border-b border-pink-100">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold tracking-tight text-pink-800">Shop by Brand</h1>
        </div>
    </div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
        @if($brands->isNotEmpty())
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 sm:gap-6">
                @foreach($brands as $brand)
                    <a href="{{ route('brands.show', $brand->slug) }}"
                       class="block p-4 bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow text-center group border border-transparent hover:border-pink-300">
                        @if($brand->logo_url)
                            <img src="{{ $brand->logo_url }}" alt="{{ $brand->name }} Logo" class="w-20 h-20 mx-auto mb-3 object-contain">
                        @else
                            <div class="w-20 h-20 mx-auto mb-3 bg-pink-50 rounded-full flex items-center justify-center">
                                <x-heroicon-o-tag class="w-10 h-10 text-pink-400"/> {{-- Placeholder icon --}}
                            </div>
                        @endif
                        <span class="text-sm font-medium text-gray-700 group-hover:text-pink-600">{{ $brand->name }}</span>
                        @if($brand->products_count > 0)
                           <span class="block text-xs text-gray-400 group-hover:text-pink-500">({{ $brand->products_count }} {{ Str::plural('Product', $brand->products_count) }})</span>
                        @endif
                    </a>
                @endforeach
            </div>
            @if ($brands->hasPages())
                <div class="mt-12">
                    {{ $brands->links() }}
                </div>
            @endif
        @else
            <p class="text-center text-gray-500 py-10">No brands available at the moment.</p>
        @endif
    </div>
</x-app-layout>