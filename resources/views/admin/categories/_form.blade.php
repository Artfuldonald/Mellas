
@csrf {{-- CSRF Token --}}
    
{{-- Category Name --}}
<div>
    <label for="name" class="block text-sm font-medium text-gray-700">
        Category Name <span class="text-red-500">*</span>
    </label>
    <div class="mt-1">
        <input
            type="text"
            name="name"
            id="name"
            class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md @error('name') border-red-500 @enderror"
            value="{{ old('name', $category->name ?? '') }}"
            required
            aria-describedby="name-description name-error"
        >
    </div>
    <p class="mt-2 text-sm text-gray-500" id="name-description">The name of the category. Must be unique.</p>
    @error('name')
        <p class="mt-2 text-sm text-red-600" id="name-error">{{ $message }}</p>
    @enderror
</div>

{{-- Category Description --}}
<div>
    <label for="description" class="block text-sm font-medium text-gray-700">
        Description
    </label>
    <div class="mt-1">
        <textarea
            id="description"
            name="description"
            rows="4"
            class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border border-gray-300 rounded-md @error('description') border-red-500 @enderror"
             aria-describedby="description-description description-error"
        >{{ old('description', $category->description ?? '') }}</textarea>
    </div>
     <p class="mt-2 text-sm text-gray-500" id="description-description">Optional: A brief description of the category.</p>
     @error('description')
        <p class="mt-2 text-sm text-red-600" id="description-error">{{ $message }}</p>
    @enderror
</div>

{{-- Add other fields from your Category model if needed --}}

<div class="flex justify-end space-x-3">
     <a href="{{ route('admin.categories.index') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
        Cancel
    </a>
    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
         {{ isset($category) ? 'Update Category' : 'Create Category' }}
    </button>
</div>
