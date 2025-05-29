@props([
    'category' 
])

@php
    $categoryName = $category->name ?? 'Category';
    // Assuming your Category model has an 'image_url' accessor or 'image' field
    $imageUrl = $category->image_url ?? ($category->image ? Storage::url($category->image) : asset('images/category_placeholder.png'));
    $categoryUrl = route('products.index', ['category' => $category->slug]);
@endphp

{{-- Target width roughly 231.5px. Max-width for responsiveness. --}}
<a href="{{ $categoryUrl }}" class="block bg-white rounded shadow hover:shadow-md transition-shadow duration-200 overflow-hidden group w-full max-w-[232px]">
    <div class="relative aspect-[4/3] sm:aspect-square"> {{-- Consistent aspect ratio --}}
        <img src="{{ $imageUrl }}" alt="{{ $categoryName }}" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
        {{-- Dark overlay for text readability --}}
        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/30 to-transparent"></div>
        {{-- Category Name Overlaid --}}
        <div class="absolute bottom-0 left-0 right-0 p-2 sm:p-3 text-center">
            <h3 class="text-sm sm:text-base font-semibold text-white leading-tight group-hover:text-pink-200 transition-colors">
                {{ $categoryName }}
            </h3>
        </div>
    </div>
</a>