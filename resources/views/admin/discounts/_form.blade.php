{{-- resources/views/admin/discounts/_form.blade.php --}}
{{-- Expects $discount --}}

@csrf
@if ($errors->any())
    <div class="mb-4 rounded-md border border-red-200 bg-red-50 p-4">
        <h3 class="text-sm font-medium text-red-800">Please fix the following errors:</h3>
        <ul class="mt-2 list-inside list-disc text-sm text-red-700">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="px-4 py-5 sm:p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
    {{-- Column 1 --}}
    <div class="space-y-6">
        {{-- Code --}}
        <div>
            <x-input-label for="code" :value="__('Discount Code')" />
            <x-text-input id="code" name="code" type="text" class="mt-1 block w-full uppercase"
                          :value="old('code', $discount->code ?? '')"
                          required placeholder="e.g., SUMMER10"/>
            <x-input-error class="mt-2" :messages="$errors->get('code')" />
            <p class="mt-1 text-xs text-gray-500">The code customers enter at checkout (unique, uppercase recommended).</p>
        </div>

        {{-- Type --}}
        <div>
            <x-input-label for="type" :value="__('Discount Type')" />
            <select id="type" name="type" required class="mt-1 block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                <option value="{{ \App\Models\Discount::TYPE_PERCENTAGE }}"
                        {{ old('type', $discount->type ?? \App\Models\Discount::TYPE_PERCENTAGE) == \App\Models\Discount::TYPE_PERCENTAGE ? 'selected' : '' }}>
                    Percentage (%)
                </option>
                <option value="{{ \App\Models\Discount::TYPE_FIXED }}"
                        {{ old('type', $discount->type ?? \App\Models\Discount::TYPE_PERCENTAGE) == \App\Models\Discount::TYPE_FIXED ? 'selected' : '' }}>
                    Fixed Amount ($)
                </option>
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('type')" />
        </div>

        {{-- Value --}}
        <div>
            <x-input-label for="value" :value="__('Value ($ or %)')" />
            <x-text-input id="value" name="value" type="number" step="0.01" min="0" class="mt-1 block w-full"
                          :value="old('value', $discount->value ?? '')"
                          required placeholder="e.g., 10 or 15.50"/>
            <x-input-error class="mt-2" :messages="$errors->get('value')" />
            <p class="mt-1 text-xs text-gray-500">Enter percentage (e.g., 10 for 10%) or fixed amount (e.g., 15.50).</p>
        </div>

         {{-- Description --}}
        <div>
            <x-input-label for="description" :value="__('Description (Internal)')" />
            <textarea id="description" name="description" rows="3"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                      placeholder="e.g., Summer Sale 2024 - All T-shirts"
            >{{ old('description', $discount->description ?? '') }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('description')" />
        </div>

    </div>

    {{-- Column 2 --}}
    <div class="space-y-6">
         {{-- Minimum Spend --}}
        <div>
            <x-input-label for="min_spend" :value="__('Minimum Spend ($) (Optional)')" />
            <x-text-input id="min_spend" name="min_spend" type="number" step="0.01" min="0" class="mt-1 block w-full"
                          :value="old('min_spend', $discount->min_spend ?? '')"
                          placeholder="e.g., 50.00"/>
            <x-input-error class="mt-2" :messages="$errors->get('min_spend')" />
            <p class="mt-1 text-xs text-gray-500">Minimum order subtotal required to use this code.</p>
        </div>

        {{-- Max Uses (Total) --}}
        <div>
            <x-input-label for="max_uses" :value="__('Maximum Total Uses (Optional)')" />
            <x-text-input id="max_uses" name="max_uses" type="number" step="1" min="1" class="mt-1 block w-full"
                          :value="old('max_uses', $discount->max_uses ?? '')"
                          placeholder="e.g., 1000"/>
            <x-input-error class="mt-2" :messages="$errors->get('max_uses')" />
            <p class="mt-1 text-xs text-gray-500">Total number of times this code can be used across all customers.</p>
        </div>

         {{-- Max Uses (Per User) --}}
        <div>
            <x-input-label for="max_uses_per_user" :value="__('Maximum Uses Per Customer (Optional)')" />
            <x-text-input id="max_uses_per_user" name="max_uses_per_user" type="number" step="1" min="1" class="mt-1 block w-full"
                          :value="old('max_uses_per_user', $discount->max_uses_per_user ?? '')"
                          placeholder="e.g., 1"/>
            <x-input-error class="mt-2" :messages="$errors->get('max_uses_per_user')" />
            <p class="mt-1 text-xs text-gray-500">How many times a single customer can use this code.</p>
        </div>

         {{-- Starts At --}}
        <div>
            <x-input-label for="starts_at" :value="__('Starts At (Optional)')" />
            <x-text-input id="starts_at" name="starts_at" type="datetime-local" class="mt-1 block w-full"
                          :value="old('starts_at', $discount->starts_at ? $discount->starts_at->format('Y-m-d\TH:i') : '')" />
            <x-input-error class="mt-2" :messages="$errors->get('starts_at')" />
            <p class="mt-1 text-xs text-gray-500">Date and time the discount becomes active.</p>
        </div>

         {{-- Expires At --}}
        <div>
            <x-input-label for="expires_at" :value="__('Expires At (Optional)')" />
            <x-text-input id="expires_at" name="expires_at" type="datetime-local" class="mt-1 block w-full"
                          :value="old('expires_at', $discount->expires_at ? $discount->expires_at->format('Y-m-d\TH:i') : '')" />
            <x-input-error class="mt-2" :messages="$errors->get('expires_at')" />
             <p class="mt-1 text-xs text-gray-500">Date and time the discount expires.</p>
        </div>

         {{-- Active Status --}}
        <div class="relative flex items-start pt-4 border-t border-gray-200">
            <div class="flex h-6 items-center">
                <input id="is_active" name="is_active" type="checkbox" value="1"
                       class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                       {{ old('is_active', $discount->is_active ?? true) ? 'checked' : '' }}>
            </div>
            <div class="ml-3 text-sm leading-6">
                <x-input-label for="is_active" :value="__('Active')" class="font-medium !text-gray-900"/>
                <p class="text-gray-500">Make this discount code usable.</p>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('is_active')" />
        </div>

    </div>
</div>