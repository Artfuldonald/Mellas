<x-admin-layout :title="'Edit Attribute: ' . $attribute->name">

    <div class="py-8 px-4 sm:px-6 lg:px-8 space-y-6"> {{-- Removed max-w-4xl mx-auto here, can be added back if desired for overall page constraint --}}
        <div class="sm:flex sm:items-center sm:justify-between mb-6"> {{-- Flex container for title and back link --}}
            <div class="min-w-0 flex-1">
                <h1 class="text-2xl font-semibold text-gray-900">Edit Attribute: <span class="text-pink-600">{{ $attribute->name }}</span></h1> {{-- Changed indigo to pink for consistency --}}
            </div>
            <div class="mt-4 sm:mt-0 sm:ml-4">
                <a href="{{ route('admin.attributes.index') }}" class="inline-flex items-center text-sm font-medium text-pink-600 hover:text-pink-800">
                    <x-heroicon-s-arrow-left class="w-4 h-4 mr-1.5" />
                    Back to Attributes
                </a>
            </div>
        </div>

         @include('admin.partials._session_messages')

        <form action="{{ route('admin.attributes.update', $attribute->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="bg-white shadow sm:rounded-lg divide-y divide-gray-200">
                {{-- Attribute Name Section --}}
                <div class="px-4 py-5 sm:p-6">
                     <h3 class="text-lg font-medium leading-6 text-gray-900 mb-1">Attribute Details</h3>
                     {{-- Pass $attribute to the form include --}}
                     @include('admin.attributes._form', ['attribute' => $attribute])
                </div>

                {{-- Attribute Values Section --}}
                <div class="px-4 py-5 sm:p-6">
                     <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Attribute Values</h3>

                     <div id="attribute-values-container" class="space-y-4">
                        @forelse($attribute->values as $index => $value)
                            <div class="flex items-center space-x-3 value-entry">
                                 <input type="hidden" name="values[{{ $index }}][id]" value="{{ $value->id }}">
                                 <div class="flex-1">
                                     <label for="value_{{ $value->id }}" class="sr-only">Value</label>
                                     <input type="text" name="values[{{ $index }}][value]" id="value_{{ $value->id }}"
                                            value="{{ old("values.{$index}.value", $value->value) }}" required placeholder="Enter value (e.g., Red, Small)"
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 sm:text-sm @error("values.{$index}.value") border-red-500 @enderror">
                                     @error("values.{$index}.value") <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                 </div>
                                 <div class="flex items-center">
                                      <input type="checkbox" name="delete_values[]" id="delete_value_{{ $value->id }}" value="{{ $value->id }}"
                                             class="h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500">
                                      <label for="delete_value_{{ $value->id }}" class="ml-2 text-sm text-gray-500" title="Mark for deletion">Delete</label>
                                 </div>
                             </div>
                        @empty
                            <p class="text-sm text-gray-500 italic">No values added yet.</p>
                        @endforelse

                        <template id="new-value-template">
                             <div class="flex items-center space-x-3 value-entry new-value-entry">
                                 <div class="flex-1">
                                     <label class="sr-only">New Value</label>
                                     <input type="text" name="new_values[][value]" {{-- Array syntax for new values --}}
                                            required placeholder="Enter new value"
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 sm:text-sm">
                                 </div>
                                 <button type="button" onclick="this.closest('.value-entry').remove()"
                                         class="inline-flex items-center justify-center p-1.5 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" title="Remove">
                                     <x-heroicon-s-x-mark class="h-4 w-4" /> {{-- Changed to X-Mark for consistency --}}
                                 </button>
                             </div>
                        </template>
                     </div>

                     <button type="button" id="add-value-button"
                             class="mt-4 inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500">
                         <x-heroicon-s-plus class="-ml-1 mr-2 h-5 w-5 text-gray-400" /> {{-- Changed to solid plus --}}
                        Add Value
                     </button>
                     @error('new_values.*.value') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                     @error('delete_values.*') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="bg-gray-50 px-4 py-3 text-right sm:px-6">
                     <a href="{{ route('admin.attributes.index') }}" class="rounded-md bg-white py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500">
                        Cancel
                    </a>
                    <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-pink-600 hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500"> {{-- Changed indigo to pink --}}
                        Update Attribute
                    </button>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('attribute-values-container');
            const template = document.getElementById('new-value-template');
            const addButton = document.getElementById('add-value-button');

            if (container && template && addButton) {
                addButton.addEventListener('click', function() {
                    const clone = template.content.cloneNode(true);
                    const newIndex = Date.now(); // Use timestamp for a somewhat unique index for new items
                    const input = clone.querySelector('input[name^="new_values"]');
                    if (input) {
                        // Update name to ensure it's unique enough for validation and old() helper
                        // Although for new_values[][value], uniqueness of index isn't as critical as for existing `values[index][value]`
                        input.name = `new_values[${newIndex}][value]`;
                    }
                    container.appendChild(clone);
                    const newInputField = container.querySelector('.new-value-entry:last-of-type input[type="text"]');
                    if (newInputField) {
                        newInputField.focus();
                    }
                });
            } else {
                console.warn("Attribute values dynamic elements not found. Ensure IDs are correct: attribute-values-container, new-value-template, add-value-button");
            }
        });
    </script>
    @endpush

</x-admin-layout>