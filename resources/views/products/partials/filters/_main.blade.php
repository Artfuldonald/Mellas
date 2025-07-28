{{-- views/products/partials/filters/_main.php --}}
<div class="divide-y divide-gray-200">
    <!-- Category Filter Link -->
    <div @click="navigateTo('category')" class="px-4 py-3 cursor-pointer">
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-900">Category</span>
            <x-heroicon-o-chevron-right class="h-5 w-5 text-gray-400"/>
        </div>
        <p class="text-xs text-gray-500 mt-1 truncate" x-text="getCategoryName(filters.category) || 'All'"></p>
    </div>

    <!-- Brand Filter Link -->
    <div @click="navigateTo('brand')" class="px-4 py-3 cursor-pointer">
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-900">Brand</span>
            <x-heroicon-o-chevron-right class="h-5 w-5 text-gray-400"/>
        </div>
        <p class="text-xs text-gray-500 mt-1 truncate" x-text="getBrandNames(filters.brands).join(', ') || 'All'"></p>
    </div>

    <!-- Price Range Filter -->
    <div class="px-4 py-4">
        <span class="text-sm font-medium text-gray-900">Price (GH₵)</span>
        <div class="mt-2 flex items-center space-x-2">
            <input x-model.debounce.500ms="filters.price_min" type="number" placeholder="Min" class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-pink-500 focus:border-pink-500">
            <span class="text-gray-500">-</span>
            <input x-model.debounce.500ms="filters.price_max" type="number" placeholder="Max" class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-pink-500 focus:border-pink-500">
        </div>
    </div>

    <!-- Discount Filter (Inline) -->
    <div class="px-4 py-4">
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-900">Discount Percentage</span>            
            <button @click="filters.discount_min = null" x-show="filters.discount_min" type="button" class="text-xs text-pink-600 hover:underline">Clear</button>
        </div>
        <ul class="space-y-2 mt-2">
            @foreach([20, 50, 70] as $discount)
                <li class="flex items-center">
                    <input x-model="filters.discount_min" type="radio" id="discount_{{ $discount }}" value="{{ $discount }}" class="h-4 w-4 rounded-full border-gray-300 text-pink-600 focus:ring-pink-500">
                    <label for="discount_{{ $discount }}" class="ml-3 text-sm text-gray-600">{{ $discount }}% or more</label>
                </li>
            @endforeach
        </ul>
    </div>
    
    <!-- Gender Filter (Inline Example) -->
        <div class="px-4 py-4">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-900">Gender</span>
                <button @click="filters.gender = null" x-show="filters.gender" type="button" class="text-xs text-pink-600 hover:underline">Clear</button>
            </div>
            {{-- ... your gender radio buttons ... --}}
        </div>        

     <!-- Product Rating Filter -->
    <div class="px-4 py-4">
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-900">Product Rating</span>
            <button @click="filters.rating_min = null" x-show="filters.rating_min" type="button" class="text-xs text-pink-600 hover:underline">Clear</button>
        </div>
        <ul class="space-y-2 mt-2">
            @for ($i = 4; $i >= 1; $i--)
            <li class="flex items-center">
                <input x-model="filters.rating_min" type="radio" id="rating_{{ $i }}" value="{{ $i }}" class="h-4 w-4 rounded-full border-gray-300 text-pink-600 focus:ring-pink-500">
                <label for="rating_{{ $i }}" class="ml-3 text-sm text-gray-600 flex items-center">
                    @for ($s = 1; $s <= 5; $s++)
                        <svg class="w-4 h-4 {{ $s <= $i ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                    @endfor
                    <span class="ml-1.5">& above</span>
                </label>
            </li>
            @endfor
        </ul>
    </div>
</div>