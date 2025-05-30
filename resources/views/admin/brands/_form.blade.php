{{-- Partial for create and edit forms FOR ADMIN BRAND--}}
<div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
    <div class="sm:col-span-4">
        <label for="name" class="block text-sm font-medium text-gray-700">Brand Name <span class="text-red-500">*</span></label>
        <input type="text" name="name" id="name" required
               value="{{ old('name', $brand->name ?? '') }}"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 sm:text-sm @error('name') border-red-500 @enderror"
               placeholder="e.g., Apple, Samsung, Nike">
        @error('name')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-6">
        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
        <textarea name="description" id="description" rows="3"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 sm:text-sm @error('description') border-red-500 @enderror"
                  placeholder="Brief description of the brand.">{{ old('description', $brand->description ?? '') }}</textarea>
        @error('description')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-3">
        <label for="logo" class="block text-sm font-medium text-gray-700">Brand Logo</label>
        <input type="file" name="logo" id="logo" accept="image/*"
               class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-pink-50 file:text-pink-700 hover:file:bg-pink-100 @error('logo') border-red-500 @enderror">
        @error('logo')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
        @if(isset($brand) && $brand->logo_url)
            <div class="mt-2">
                <img src="{{ $brand->logo_url }}" alt="{{ $brand->name }} Logo" class="h-16 w-auto rounded">
                <div class="mt-1 flex items-center text-xs">
                    <input type="checkbox" name="remove_logo" id="remove_logo" value="1" class="h-3.5 w-3.5 rounded border-gray-300 text-red-600 focus:ring-red-500">
                    <label for="remove_logo" class="ml-1.5 text-gray-700">Remove current logo</label>
                </div>
            </div>
        @endif
        <p class="mt-1 text-xs text-gray-500">Recommended: PNG, JPG, WEBP, SVG. Max 1MB.</p>
    </div>

    <div class="sm:col-span-6">
        <div class="flex items-start">
            <div class="flex h-5 items-center">
                <input id="is_active" name="is_active" type="checkbox" value="1"
                       {{ old('is_active', $brand->is_active ?? true) ? 'checked' : '' }}
                       class="h-4 w-4 rounded border-gray-300 text-pink-600 focus:ring-pink-500">
            </div>
            <div class="ml-3 text-sm">
                <label for="is_active" class="font-medium text-gray-700">Active</label>
                <p class="text-gray-500 text-xs">Inactive brands will not be shown to customers.</p>
            </div>
        </div>
    </div>
</div>
```