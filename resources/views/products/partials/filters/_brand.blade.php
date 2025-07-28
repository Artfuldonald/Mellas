{{-- views/products/partials/filters/_brand.php --}}
<div class="p-4">
    <input type="search" x-model="brandSearch" placeholder="Search brand..." class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-pink-500 focus:border-pink-500">
    <ul class="mt-4 space-y-2 max-h-80 overflow-y-auto">
        <template x-for="brand in filteredBrands" :key="brand.id">
            <li class="flex items-center">
                <input x-model="filters.brands" :id="'brand_'+brand.id" :value="brand.slug" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-pink-600 focus:ring-pink-500">
                <label :for="'brand_'+brand.id" class="ml-3 text-sm text-gray-600" x-text="brand.name"></label>
            </li>
        </template>
    </ul>
</div>