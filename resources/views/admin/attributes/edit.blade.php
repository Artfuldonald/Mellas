<x-admin-layout :title="'Edit Attribute: ' . $attribute->name">

    <div class="py-8 px-4 sm:px-6 lg:px-8 space-y-6">
        <h1 class="text-2xl font-semibold text-gray-900">Edit Attribute: <span class="text-indigo-600">{{ $attribute->name }}</span></h1>

         {{-- Add the session message display here --}}
         @include('admin.partials._session_messages')
         
        <form action="{{ route('admin.attributes.update', $attribute->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="bg-white shadow sm:rounded-lg divide-y divide-gray-200">
                {{-- Attribute Name Section --}}
                <div class="px-4 py-5 sm:p-6">
                     <h3 class="text-lg font-medium leading-6 text-gray-900 mb-1">Attribute Details</h3>
                     @include('admin.attributes._form', ['attribute' => $attribute])
                </div>

                {{-- Attribute Values Section --}}
                <div class="px-4 py-5 sm:p-6">
                     <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Attribute Values</h3>

                     <div id="attribute-values-container" class="space-y-4">
                        {{-- Loop through existing values --}}
                        @forelse($attribute->values as $index => $value)
                            <div class="flex items-center space-x-3 value-entry">
                                 {{-- Hidden ID for existing values --}}
                                 <input type="hidden" name="values[{{ $index }}][id]" value="{{ $value->id }}">

                                 <div class="flex-1">
                                     <label for="value_{{ $value->id }}" class="sr-only">Value</label>
                                     <input type="text" name="values[{{ $index }}][value]" id="value_{{ $value->id }}"
                                            value="{{ old("values.{$index}.value", $value->value) }}" required placeholder="Enter value (e.g., Red, Small)"
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error("values.{$index}.value") border-red-500 @enderror">
                                     @error("values.{$index}.value") <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                 </div>
                                 {{-- Checkbox to mark for deletion --}}
                                 <div class="flex items-center">
                                      <input type="checkbox" name="delete_values[]" id="delete_value_{{ $value->id }}" value="{{ $value->id }}"
                                             class="h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500">
                                      <label for="delete_value_{{ $value->id }}" class="ml-2 text-sm text-gray-500" title="Mark for deletion">Delete</label>
                                 </div>
                             </div>
                        @empty
                            <p class="text-sm text-gray-500 italic">No values added yet.</p>
                        @endforelse

                        {{-- Template for adding new values (hidden) --}}
                        <template id="new-value-template">
                             <div class="flex items-center space-x-3 value-entry new-value-entry">
                                 <div class="flex-1">
                                     <label class="sr-only">New Value</label>
                                     <input type="text" name="new_values[][value]" required placeholder="Enter new value"
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                 </div>
                                 <button type="button" onclick="this.closest('.value-entry').remove()"
                                         class="inline-flex items-center justify-center p-1.5 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" title="Remove">
                                     <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                 </button>
                             </div>
                        </template>
                     </div>

                      {{-- Button to add new value row --}}
                     <button type="button" id="add-value-button"
                             class="mt-4 inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                         <svg class="-ml-1 mr-2 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Add Value
                     </button>
                     @error('new_values.*.value') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                      @error('delete_values.*') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Form Actions --}}
                <div class="bg-gray-50 px-4 py-3 text-right sm:px-6">
                     <a href="{{ route('admin.attributes.index') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Cancel
                    </a>
                    <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
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
                    // Clone the template content
                    const clone = template.content.cloneNode(true);
                    // Append the cloned row to the container
                    container.appendChild(clone);

                     // Focus the new input field
                    const newInput = container.querySelector('.new-value-entry:last-of-type input[type="text"]');
                    if (newInput) {
                        newInput.focus();
                    }
                });
            } else {
                console.error("Could not find container, template, or add button for attribute values.");
            }
        });
    </script>
    @endpush

</x-admin-layout>