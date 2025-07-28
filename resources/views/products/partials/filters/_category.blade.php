{{-- views/products/partials/filters/_category.php --}}
<div>
    <!-- Breadcrumb for nested categories -->
    <div class="px-4 py-2 bg-gray-50 text-xs text-gray-500 flex items-center space-x-1" x-show="categoryDrilldownStack.length > 0">
        <span @click="resetCategoryDrilldown()" class="hover:underline cursor-pointer">All Categories</span>
        <template x-for="(cat, index) in categoryDrilldownStack" :key="cat.id">
            <div class="flex items-center space-x-1">
                <span>/</span>
                <span @click="popCategoryDrilldown(index)" class="hover:underline cursor-pointer" x-text="cat.name"></span>
            </div>
        </template>
    </div>
    
    <ul class="divide-y divide-gray-200">
        <template x-for="category in currentCategoryList" :key="category.id">
            <li @click="selectCategory(category)" class="p-4 flex items-center justify-between cursor-pointer" :class="{ 'bg-pink-50': filters.category === category.slug }">
                <div class="flex items-center">
                    <input x-model="filters.category" :id="'cat_'+category.id" :value="category.slug" @click.stop type="radio" class="h-4 w-4 rounded-full border-gray-300 text-pink-600 focus:ring-pink-500">
                    <label :for="'cat_'+category.id" class="ml-3 text-sm text-gray-600" x-text="category.name"></label>
                </div>
                <button @click.stop="drillDown(category)" x-show="category.children && category.children.length > 0" class="p-1">
                    <x-heroicon-o-chevron-right class="h-5 w-5 text-gray-400"/>
                </button>
            </li>
        </template>
    </ul>
</div>