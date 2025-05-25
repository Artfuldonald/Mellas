{{-- resources/views/admin/products/stock/adjust.blade.php --}}
<x-admin-layout :title="'Adjust Stock: ' . $adjustableName">

    <div class="py-8 px-4 sm:px-6 lg:px-8 space-y-6 max-w-xl mx-auto">
        {{-- Header & Back Button --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Adjust Stock</h1>
                {{-- Display Product or Product + Variant name --}}
                <p class="text-sm text-gray-500">For: {{ $adjustableName }}</p>
            </div>
            {{-- Link back to the main product edit page --}}
            <a href="{{ route('admin.products.edit', $product) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                ← Back to Product Edit
            </a>
        </div>

        {{-- Session Messages --}}
        @include('admin.partials._session_messages')

        {{-- Adjustment Form Card --}}
        <div class="bg-white shadow sm:rounded-lg">
            {{-- Determine the correct form action URL based on whether $variant exists --}}
            @php
                $formAction = $variant
                    ? route('admin.products.variants.stock.adjust', [$product, $variant])
                    : route('admin.products.stock.adjust', $product);
            @endphp
            <form action="{{ $formAction }}" method="POST">
                @csrf {{-- Use POST, controller handles logic --}}

                <div class="px-4 py-5 sm:p-6 space-y-6">

                    {{-- Current Stock (Read Only) --}}
                    <div>
                        <x-input-label for="current_stock" value="Current Stock Level" />
                        <x-text-input id="current_stock" type="number" class="mt-1 block w-full bg-gray-100"
                                      :value="$currentStock"
                                      disabled readonly />
                        <p class="mt-1 text-xs text-gray-500">The quantity currently recorded in the system.</p>
                    </div>

                    {{-- Quantity Change --}}
                    <div>
                        <x-input-label for="quantity_change" value="Quantity Change (+/-)" />
                        <x-text-input id="quantity_change" name="quantity_change" type="number" step="1"
                                      class="mt-1 block w-full"
                                      :value="old('quantity_change', 0)"
                                      required autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('quantity_change')" />
                        <p class="mt-1 text-xs text-gray-500">Enter a positive number to increase stock (e.g., 10) or a negative number to decrease stock (e.g., -5).</p>
                    </div>

                    {{-- Reason for Adjustment --}}
                    <div>
                         <x-input-label for="reason" value="Reason for Adjustment" />
                         {{-- Consider making this a dropdown for consistency --}}
                         <select id="reason" name="reason" required
                                 class="mt-1 block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                             <option value="" {{ old('reason') == '' ? 'selected' : '' }} disabled>-- Select a Reason --</option>
                             <option value="Stocktake Correction" {{ old('reason') == 'Stocktake Correction' ? 'selected' : '' }}>Stocktake Correction</option>
                             <option value="Damaged Goods" {{ old('reason') == 'Damaged Goods' ? 'selected' : '' }}>Damaged Goods</option>
                             <option value="Returned Item (Restocked)" {{ old('reason') == 'Returned Item (Restocked)' ? 'selected' : '' }}>Returned Item (Restocked)</option>
                             <option value="Promotion / Giveaway" {{ old('reason') == 'Promotion / Giveaway' ? 'selected' : '' }}>Promotion / Giveaway</option>
                             <option value="Lost / Stolen" {{ old('reason') == 'Lost / Stolen' ? 'selected' : '' }}>Lost / Stolen</option>
                             <option value="Other" {{ old('reason') == 'Other' ? 'selected' : '' }}>Other</option>
                         </select>
                         {{-- Or use a simple text input:
                         <x-text-input id="reason" name="reason" type="text" class="mt-1 block w-full"
                                       :value="old('reason')" required placeholder="e.g., Stocktake, Damage, Return" />
                         --}}
                         <x-input-error class="mt-2" :messages="$errors->get('reason')" />
                         <p class="mt-1 text-xs text-gray-500">Select the primary reason for this stock change.</p>
                    </div>

                     {{-- Optional Notes --}}
                    <div>
                        <x-input-label for="notes" value="Notes (Optional)" />
                        <textarea id="notes" name="notes" rows="3"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                  placeholder="Add any extra details about this adjustment..."
                        >{{ old('notes') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                    </div>

                </div>

                {{-- Action Buttons --}}
                <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-end space-x-3">
                     <a href="{{ route('admin.products.edit', $product) }}" class="inline-flex justify-center rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Cancel
                    </a>
                    <x-primary-button type="submit">
                        {{ __('Adjust Stock Level') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>