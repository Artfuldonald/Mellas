<?php

namespace App\Http\Controllers\Admin;

use App\Models\Attribute;
use App\Models\AttributeValue; // Import
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;      // Import
use Illuminate\Validation\Rule; // Import

class AttributeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $attributes = Attribute::withCount('values')->latest()->paginate(15);
        return view('admin.attributes.index', compact('attributes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.attributes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:attributes,name',
            // Slug is generated automatically by the model
        ]);

        Attribute::create($validated);

        return redirect()->route('admin.attributes.index')
               ->with('success', 'Attribute created successfully.');
    }

    /**
     * Display the specified resource. (Redirect to edit)
     */
    public function show(Attribute $attribute)
    {
        return redirect()->route('admin.attributes.edit', $attribute);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Attribute $attribute)
    {
        // Eager load values for the form
        $attribute->load('values');
        return view('admin.attributes.edit', compact('attribute'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Attribute $attribute)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('attributes')->ignore($attribute->id),
            ],
            // Slug generation is handled by model if name changes
            // Validate the values array
            'values' => 'nullable|array',
            // Validate each existing value being updated
            'values.*.id' => 'nullable|integer|exists:attribute_values,id,attribute_id,'.$attribute->id, // Ensure ID belongs to this attribute
            'values.*.value' => ['required','string','max:255', function ($inputKey, $value, $fail) use ($request, $attribute) {
                 // Custom rule to check uniqueness WITHIN this attribute, ignoring self if ID exists
                 $index = explode('.', $inputKey)[1]; // Get index '0' from 'values.0.value'
                 $valueId = $request->input("values.{$index}.id"); // Get ID of the value being checked
                 $query = AttributeValue::where('attribute_id', $attribute->id)->where('value', $value);
                 if ($valueId) {
                     $query->where('id', '!=', $valueId); // Exclude self if updating
                 }
                 if ($query->exists()) {
                     $fail("The value ':input' already exists for this attribute.");
                 }
             }],
             // Validate new values
             'new_values' => 'nullable|array',
             'new_values.*.value' => ['required','string','max:255', function ($inputKey, $value, $fail) use ($attribute) {
                // Check uniqueness for NEW values within this attribute
                 if (AttributeValue::where('attribute_id', $attribute->id)->where('value', $value)->exists()) {
                     $fail("The new value ':input' already exists for this attribute.");
                 }
             }],
             // IDs of values to delete
             'delete_values' => 'nullable|array',
             'delete_values.*' => 'integer|exists:attribute_values,id,attribute_id,'.$attribute->id,
        ], [
            // Custom messages
            'values.*.value.required' => 'The value field is required for all items.',
            'new_values.*.value.required' => 'The value field is required for all new items.',
        ]);

        // --- Update Attribute Name ---
        $attribute->update(['name' => $validated['name']]); // Model will handle slug

        $existingValueIdsSubmitted = [];

        // --- Update Existing Values ---
        if (isset($validated['values'])) {
            foreach ($validated['values'] as $index => $valueData) {
                if (isset($valueData['id'])) { // Should always be set if validating existing
                    $valueModel = AttributeValue::find($valueData['id']);
                    if ($valueModel) {
                        $valueModel->update(['value' => $valueData['value']]); // Model handles slug
                        $existingValueIdsSubmitted[] = $valueModel->id;
                    }
                }
            }
        }

         // --- Delete Values ---
         // Method 1: Using explicit delete_values array from checkboxes
         if(isset($validated['delete_values'])) {
            // Check if these values are used in variants before deleting? Important!
             AttributeValue::whereIn('id', $validated['delete_values'])->delete();
         }
         // Method 2 (Alternative): Delete values that existed but were *not* submitted back (implies removal from UI)
         // $existingValueIds = $attribute->values->pluck('id')->toArray();
         // $valuesToDeleteIds = array_diff($existingValueIds, $existingValueIdsSubmitted);
         // if (!empty($valuesToDeleteIds)) {
         //    AttributeValue::whereIn('id', $valuesToDeleteIds)->where('attribute_id', $attribute->id)->delete();
         // }


        // --- Create New Values ---
        if (isset($validated['new_values'])) {
            foreach ($validated['new_values'] as $newValue) {
                if (!empty(trim($newValue['value']))) { // Ensure value is not just whitespace
                    $attribute->values()->create([
                        'value' => $newValue['value'],
                        // Slug generated by model
                    ]);
                }
            }
        }

        return redirect()->route('admin.attributes.edit', $attribute)
               ->with('success', 'Attribute updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attribute $attribute)
    {
        // --- IMPORTANT CHECK: Is this attribute used by any product? ---
        if ($attribute->products()->exists()) {
            return redirect()->route('admin.attributes.index')
                ->with('error', 'Cannot delete attribute: It is currently assigned to one or more products.');
        }
        // Check if any values are used by variants? More complex, maybe later.

        $attribute->delete(); // Cascade should delete associated values

        return redirect()->route('admin.attributes.index')
               ->with('success', 'Attribute deleted successfully.');
    }
}