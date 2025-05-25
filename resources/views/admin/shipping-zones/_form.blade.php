{{-- resources/views/admin/shipping-zones/_form.blade.php --}}

{{-- Display validation errors if any --}}
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
    {{-- Zone Name --}}
    <div>
        {{-- Use the x-input-label component --}}
        <x-input-label for="name" :value="__('Zone Name')" />
        {{-- Use the x-text-input component --}}
        {{-- Use old() helper, falling back to the $shippingZone's name (or empty string if new) --}}
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                      :value="old('name', $shippingZone->name ?? '')"
                      required autofocus placeholder="e.g., Nationwide, Local Area"/>
        {{-- Use the x-input-error component --}}
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
        <p class="mt-1 text-xs text-gray-500">A unique name for this shipping zone.</p>
    </div>

    {{-- Zone Definition (Placeholder for future complexity) --}}
    {{--
    <div class="border-t pt-4 mt-4">
         <h3 class="text-md font-medium text-gray-800">Zone Definition</h3>
         <p class="mt-1 text-sm text-gray-500">Define which regions this zone applies to (e.g., select countries/states). Feature coming soon.</p>
         {{-- Add inputs for countries/states here later --}}
    </div>
    

    {{-- Active Status --}}
    <div class="relative flex items-start pt-4 border-t">
        <div class="flex h-6 items-center">
            <input id="is_active" name="is_active" type="checkbox" value="1"
                   class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                   {{-- Use old(), fallback to existing value, default to true for new zones --}}
                   {{ old('is_active', $shippingZone->is_active ?? true) ? 'checked' : '' }}>
        </div>
        <div class="ml-3 text-sm leading-6">
            {{-- Use x-input-label, linking it to the checkbox --}}
            <x-input-label for="is_active" :value="__('Active')" class="font-medium !text-gray-900"/> {{-- Override default color --}}
            <p class="text-gray-500">Enable this shipping zone and its rates.</p>
        </div>
        <x-input-error class="mt-2" :messages="$errors->get('is_active')" />
    </div>

</div>