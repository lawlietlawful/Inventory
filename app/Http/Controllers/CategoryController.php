<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display categories listing with product count
     */
    public function index(): View
    {
        $categories = Category::withCount('products')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('categories.index', compact('categories'));
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_name' => ['required', 'string', 'max:100', Rule::unique(Category::class, 'name')],
        ]);

        $maxOrder = Category::max('sort_order') ?? 0;
        Category::create([
            'name'       => trim($validated['category_name']),
            'sort_order' => $maxOrder + 1,
        ]);

        return redirect()->route('categories.index')
            ->with('success', "Category \"{$validated['category_name']}\" added successfully.");
    }

    /**
     * Update an existing category
     */
    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'category_name' => [
                'required', 
                'string', 
                'max:100', 
                Rule::unique(Category::class, 'name')->ignore($category->id, 'id')
            ],
        ]);

        $category->update([
            'name' => trim($validated['category_name']),
        ]);

        return redirect()->route('categories.index')
            ->with('success', "Category updated to \"{$category->name}\".");
    }

    /**
     * Remove a category (Block delete if products exist!)
     */
    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            $count = $category->products()->count();
            return back()->with('warning', "Cannot delete category \"{$category->name}\" because it contains {$count} menu item(s). Please reassign or delete those items first.");
        }

        $name = $category->name;
        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', "Category \"{$name}\" was deleted successfully.");
    }
}
