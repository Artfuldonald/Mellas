{{-- Used in create.blade.php and edit.blade.php --}}
@csrf {{-- Add CSRF only if used in a standalone form, not needed if included --}}

<div>
    <label for="name" class="block text-sm font-medium text-gray-700">Attribute Name</label>
    <input type="text" name="name" id="name" required
           value="{{ old('name', $attribute->name ?? '') }}"
           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('name') border-red-500 @enderror"
           placeholder="e.g., Color, Size, Material">
    @error('name')
        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
    @enderror
    <p class="mt-1 text-sm text-gray-500">The main name of the attribute group.</p>
</div>