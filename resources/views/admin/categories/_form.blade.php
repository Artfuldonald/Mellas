{{-- resources/views/admin/categories/_form.blade.php --}}
@csrf {{-- CSRF Token --}}
@if(isset($category) && $category->exists)
    @method('PUT') {{-- Method spoofing for updates --}}
@endif

{{-- Display validation errors if any --}}
@if ($errors->any())
<div class="mb-6 rounded-md border border-red-200 bg-red-50 p-4">
    <h3 class="text-sm font-medium text-red-800">Please fix the following errors:</h3>
    <ul class="mt-2 list-inside list-disc text-sm text-red-700">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- Form Fields within a Card --}}
<div class="bg-white shadow sm:rounded-lg">
    <div class="px-4 py-5 sm:p-6 space-y-6">

        {{-- Category Name --}}
        <div>
            <x-input-label for="name" :value="__('Category Name')" /> <span class="text-red-500">*</span>
            <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $category->name ?? '')" required />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
            <p class="mt-2 text-sm text-gray-500">The name of the category. Must be unique.</p>
        </div>

        {{-- Slug (Optional Input) --}}
        <div class="mt-4">
            <x-input-label for="slug" :value="__('Slug (Optional)')" />
            <x-text-input id="slug" name="slug" class="mt-1 block w-full bg-gray-50" :value="old('slug', $category->slug ?? '')" placeholder="Auto-generated from name if blank"/>
            <x-input-error :messages="$errors->get('slug')" class="mt-2" />
            <p class="mt-1 text-xs text-gray-500">URL-friendly version of the name. Leave blank to auto-generate.</p>
        </div>

        {{-- Parent Category Dropdown --}}
        <div class="mt-4">
            <x-input-label for="parent_id" :value="__('Parent Category (Optional)')" />
            <select id="parent_id" name="parent_id"
                    class="mt-1 block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                <option value="">-- None (Top Level) --</option>
                @foreach($parentCategories ?? [] as $parent)
                    <option value="{{ $parent->id }}"
                            {{-- Ensure $category->parent_id is checked correctly, using null coalescing --}}
                            {{ old('parent_id', $category->parent_id ?? null) == $parent->id ? 'selected' : '' }}>
                        {{ $parent->name }}
                    </option>
                    {{-- Note: Indentation/hierarchy display logic not included here, needs helper/recursive structure --}}
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('parent_id')" />
            <p class="mt-1 text-xs text-gray-500">Assign this category under another existing category.</p>
        </div>

        {{-- Category Description --}}
        <div class="mt-4">
            <x-input-label for="description" :value="__('Description (Optional)')" />
            <div class="mt-1">
                <textarea id="description" name="description" rows="4"
                          class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border border-gray-300 rounded-md @error('description') border-red-500 @enderror"
                >{{ old('description', $category->description ?? '') }}</textarea>
            </div>
             <p class="mt-2 text-sm text-gray-500">Optional: A brief description of the category.</p>
             <x-input-error :messages="$errors->get('description')" class="mt-2" />
        </div>
       
        {{-- Category Image --}}
        <div class="mt-4">
            <x-input-label for="image" :value="__('Category Image (Optional)')" />
            @if (isset($category) && $category->image)
                <div class="mt-2 mb-2">
                    <img src="{{ Storage::url($category->image) }}" alt="{{ $category->name }}" class="h-20 w-auto rounded">
                </div>
                <p class="text-xs text-gray-500 mb-1">Current image.</p>
                {{-- ***** ADD THIS CHECKBOX ***** --}}
                <div class="relative flex items-start mb-2">
                    <div class="flex h-5 items-center">
                        <input id="remove_image" name="remove_image" type="checkbox" value="1"
                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="remove_image" class="font-medium text-gray-700">Remove current image</label>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mb-1">Upload a new image below to replace it, or check "Remove" to delete it.</p>
                {{-- *************************** --}}
            @endif
            <input type="file" name="image" id="image" accept="image/*"
                class="block w-full text-sm text-gray-500 border border-gray-300 rounded-md cursor-pointer file:mr-4 file:py-2 file:px-4 file:rounded-l-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            <x-input-error class="mt-2" :messages="$errors->get('image')" />
            <p class="mt-1 text-xs text-gray-500">Recommended size: 300x300px. Max 3MB.</p>
        </div>

        {{-- Active Status Checkbox --}}
        <div class="relative flex items-start pt-4 border-t border-gray-200 mt-6">
            <div class="flex h-6 items-center">
                <input id="is_active" name="is_active" type="checkbox" value="1"
                       class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                       {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}>
            </div>
            <div class="ml-3 text-sm leading-6">
                <x-input-label for="is_active" :value="__('Active')" class="font-medium !text-gray-900"/>
                <p class="text-gray-500">Make this category visible on the storefront.</p>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('is_active')" />
        </div>

    </div>{{-- End Card Body --}}

    {{-- Action Buttons within the Card Footer --}}
    <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-end space-x-3 rounded-b-lg">
        <a href="{{ route('admin.categories.index') }}" class="inline-flex justify-center rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
           Cancel
       </a>
       <x-primary-button type="submit">
            {{-- Check if the category exists to determine button text --}}
            {{ isset($category) && $category->exists ? 'Update Category' : 'Create Category' }}
       </x-primary-button>
   </div>

</div>{{-- End Card --}}