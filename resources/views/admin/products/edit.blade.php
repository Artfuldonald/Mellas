{{-- resources/views/admin/products/edit.blade.php --}}
<x-admin-layout :title="'Edit Product: ' . $product->name">

    <div class="py-8 px-4 sm:px-6 lg:px-8 space-y-6">
         <div class="flex items-center justify-between">
             <h1 class="text-2xl font-semibold text-gray-900">Edit Product: <span class="text-indigo-600">{{ $product->name }}</span></h1>
             <a href="{{ route('admin.products.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                ← Back to Products
            </a>
        </div>

        {{-- Session Messages --}}
         @include('admin.partials._session_messages')

         {{-- Main Product Form Card --}}
         <div class="bg-white shadow sm:rounded-lg">
            <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- Include the form partial --}}
                {{-- $product, $categories, $allAttributes are passed from controller's edit method --}}
                @include('admin.products._form', [
                    'product' => $product,
                    'categories' => $categories,
                    'allAttributes' => $allAttributes
                ])

                {{-- Buttons are inside the _form partial --}}
            </form>
        </div>

        {{-- ***** NEW: Stock Management Section ***** --}}
        <div class="bg-white shadow sm:rounded-lg mt-6">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Inventory Management</h3>
                <div class="mt-4 border-t border-gray-200 pt-4">

                    {{-- Show adjustment button only if product is NOT variant-based --}}
                    {{-- Use the same Alpine variable 'hasVariants' from the form --}}
                    <div x-data="{ hasVariants: {{ $product->variants()->exists() || $product->attributes()->exists() ? 'true' : 'false' }} }" x-show="!hasVariants">
                        <h4 class="text-md font-medium text-gray-800">Simple Product Stock</h4>
                        <p class="mt-1 text-sm text-gray-600">
                            Current Stock: <span class="font-semibold">{{ $product->quantity }}</span>
                        </p>
                        <div class="mt-3">
                            <a href="{{ route('admin.products.stock.adjust.form', $product) }}"
                               class="inline-flex items-center rounded border border-gray-300 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                <x-heroicon-o-adjustments-horizontal class="w-4 h-4 mr-1"/>
                                Adjust Stock
                            </a>
                        </div>
                    </div>

                    {{-- List Variants with Adjust Buttons --}}
                    {{-- Show only if product IS variant-based --}}
                     <div x-data="{ hasVariants: {{ $product->variants()->exists() || $product->attributes()->exists() ? 'true' : 'false' }} }" x-show="hasVariants">
                         <h4 class="text-md font-medium text-gray-800">Variant Stock</h4>
                         @if($product->variants->isNotEmpty())
                            <p class="mt-1 text-sm text-gray-500">Adjust stock for individual variants below.</p>
                            <ul role="list" class="mt-3 divide-y divide-gray-200 border-t border-b">
                                @foreach ($product->variants()->orderBy('name')->get() as $variant) {{-- Load variants again if needed, or rely on preloading --}}
                                    <li class="flex items-center justify-between py-3 px-1">
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-gray-900">{{ $variant->name ?: 'Variant #' . $variant->id }}</p>
                                            <p class="text-xs text-gray-500">SKU: {{ $variant->sku ?: 'N/A' }}</p>
                                        </div>
                                        <div class="ml-4 flex-shrink-0 flex items-center space-x-4">
                                             <span class="text-sm font-semibold {{ $variant->quantity <= 0 ? 'text-red-600' : 'text-gray-800' }}">
                                                 Stock: {{ $variant->quantity }}
                                             </span>
                                              <a href="{{ route('admin.products.variants.stock.adjust.form', [$product, $variant]) }}"
                                                 class="inline-flex items-center rounded border border-gray-300 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                                  <x-heroicon-o-adjustments-horizontal class="w-4 h-4 mr-1"/>
                                                  Adjust
                                              </a>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                         @else
                             <p class="mt-2 text-sm text-gray-500">No variants found or created yet for this product.</p>
                         @endif
                     </div>
                 </div>
            </div>
        </div>
        {{-- ***** END: Stock Management Section ***** --}}

    </div>

</x-admin-layout>