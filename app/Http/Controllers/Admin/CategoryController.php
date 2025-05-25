<?php
// app/Http/Controllers/Admin/CategoryController.php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log; // Optional: For logging
use Illuminate\Validation\Rule; // Import Rule for validation

class CategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
       
        $query = Category::with('parent')->withCount('products')->latest('id'); // Order by newest first

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

       
        $categories = $query->paginate(15)->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        
        $category = new Category([
            'is_active' => true 
        ]);
        
        $parentCategories = Category::orderBy('name')->get(['id', 'name']);

        return view('admin.categories.create', compact('category', 'parentCategories'));
    }

    /**
     * Store a newly created category in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'slug' => 'nullable|string|max:255|unique:categories,slug|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|integer|exists:categories,id',
            'is_active' => 'sometimes|boolean',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3048', // Max 3MB
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['parent_id'] = $validated['parent_id'] ?: null;

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('category-images', 'public');
        }

        try {
            Category::create($validated);
            return redirect()->route('admin.categories.index')
                             ->with('success', 'Category created successfully.');
        } catch (\Exception $e) {
             Log::error("Error creating category: " . $e->getMessage());
             return back()->with('error', 'Failed to create category.')->withInput();
        }
    }

    /**
     * Display the specified resource.
     * (Typically redirect to edit in admin panels)
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\RedirectResponse
     */
    public function show(Category $category)
    {
        
        return redirect()->route('admin.categories.edit', $category);
    }

    /**
     * Show the form for editing the specified category.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\View\View
     */
    public function edit(Category $category)
    {
        // Get potential parent categories
        // Exclude the current category itself to prevent self-parenting
        // IMPORTANT: Also exclude descendants to prevent circular loops.
        // This requires a more complex query or helper method.
        // For now, we just exclude self. Add descendant check if needed.
        $parentCategories = Category::where('id', '!=', $category->id)
                                    ->whereNotIn('id', $category->descendants()->pluck('id')->push($category->id)->all()) // More robust check
                                    ->orderBy('name')
                                    ->get(['id', 'name']);

        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    /**
     * Update the specified category in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => ['required','string','max:255', Rule::unique('categories')->ignore($category->id)],
            'slug' => ['nullable','string','max:255', Rule::unique('categories')->ignore($category->id), 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'description' => 'nullable|string',
            'parent_id' => ['nullable','integer', Rule::exists('categories', 'id')->whereNot('id', $category->id)],
            'is_active' => 'sometimes|boolean',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['parent_id'] = $validated['parent_id'] ?: null;

        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $validated['image'] = $request->file('image')->store('category-images', 'public');
        } elseif ($request->input('remove_image')) { // Add a checkbox in form to remove image
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $validated['image'] = null;
        }


        try {
            $category->update($validated);
            return redirect()->route('admin.categories.index')
                             ->with('success', 'Category updated successfully.');
        } catch (\Exception $e) {
            Log::error("Error updating category {$category->id}: " . $e->getMessage());
            return back()->with('error', 'Failed to update category.')->withInput();
        }
    }

    /**
     * Remove the specified category from storage.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Category $category)
    {
        // Add checks here if needed (e.g., prevent deleting categories with products?)
        // if ($category->products()->count() > 0) {
        //     return back()->with('error', 'Cannot delete category with associated products.');
        // }

         try {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $category->delete();
            return redirect()->route('admin.categories.index')
                             ->with('success', 'Category deleted successfully.');
        } catch (\Exception $e) {
             Log::error("Error deleting category {$category->id}: " . $e->getMessage());
             return back()->with('error', 'Failed to delete category.');
        }
    }
}