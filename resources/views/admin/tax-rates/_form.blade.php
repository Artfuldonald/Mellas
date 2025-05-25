{{-- resources/views/admin/tax-rates/_form.blade.php --}}
{{-- Expects $taxRate --}}

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

<div class="px-4 py-5 sm:p-6 space-y-6">
    {{-- Rate Name --}}
    <div>
        <x-input-label for="name" :value="__('Tax Rate Name')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                      :value="old('name', $taxRate->name ?? '')"
                      required autofocus placeholder="e.g., Standard VAT, GST, Sales Tax"/>
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
        <p class="mt-1 text-xs text-gray-500">A unique name for this tax rate.</p>
    </div>

    {{-- Rate Percentage --}}
    <div>
        <x-input-label for="rate_percent" :value="__('Rate (%)')" />
         <div class="relative mt-1 rounded-md shadow-sm">
            {{-- Convert stored decimal back to percentage for display --}}
            <x-text-input id="rate_percent" name="rate_percent" type="number" step="0.01" min="0" max="100" class="block w-full rounded-md pr-12 sm:text-sm"
                          :value="old('rate_percent', isset($taxRate->rate) ? number_format($taxRate->rate * 100, 2, '.', '') : '0.00')"
                          required placeholder="e.g., 7.00 or 20.00"/>
             <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                 <span class="text-gray-500 sm:text-sm">%</span>
            </div>
         </div>
        <x-input-error class="mt-2" :messages="$errors->get('rate_percent')" />
         <p class="mt-1 text-xs text-gray-500">Enter the tax rate as a percentage (e.g., 7 for 7%).</p>
    </div>

    {{-- Priority --}}
    <div>
        <x-input-label for="priority" :value="__('Priority')" />
        <x-text-input id="priority" name="priority" type="number" step="1" min="1" class="mt-1 block w-full"
                      :value="old('priority', $taxRate->priority ?? 1)"
                      required />
        <x-input-error class="mt-2" :messages="$errors->get('priority')" />
        <p class="mt-1 text-xs text-gray-500">Used for compound taxes (lower number applies first). Usually 1.</p>
    </div>

    {{-- Optional: Apply to Shipping Checkbox --}}
    {{--
    <div class="relative flex items-start pt-4 border-t">
        <div class="flex h-6 items-center">
            <input id="apply_to_shipping" name="apply_to_shipping" type="checkbox" value="1"
                   class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                   {{ old('apply_to_shipping', $taxRate->apply_to_shipping ?? false) ? 'checked' : '' }}>
        </div>
        <div class="ml-3 text-sm leading-6">
            <x-input-label for="apply_to_shipping" :value="__('Apply to Shipping Cost')" class="font-medium !text-gray-900"/>
            <p class="text-gray-500">If checked, this tax will also be applied to the shipping cost.</p>
        </div>
        <x-input-error class="mt-2" :messages="$errors->get('apply_to_shipping')" />
    </div>
    --}}

    {{-- Active Status --}}
    <div class="relative flex items-start pt-4 border-t">
        <div class="flex h-6 items-center">
            <input id="is_active" name="is_active" type="checkbox" value="1"
                   class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                   {{ old('is_active', $taxRate->is_active ?? true) ? 'checked' : '' }}>
        </div>
        <div class="ml-3 text-sm leading-6">
            <x-input-label for="is_active" :value="__('Active')" class="font-medium !text-gray-900"/>
            <p class="text-gray-500">Enable this tax rate.</p>
        </div>
        <x-input-error class="mt-2" :messages="$errors->get('is_active')" />
    </div>

</div>