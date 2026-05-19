<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index(Request $request): Response
    {
        $categories = $request->user()
            ->categories()
            ->orderBy('type')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('CategoriesIndex', [
            'categories' => $categories,
        ]);
    }

    /**
     * Show the form for creating a new category.
     */
    public function create(): Response
    {
        return Inertia::render('CreateCategory');
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $request->user()
            ->categories()
            ->create($validated);

        return Redirect::route('categories.index')
            ->with('success', 'Kategori berhasil ditambahkan');
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Category $category): Response
    {
        $this->authorizeUser($category);

        return Inertia::render('EditCategory', [
            'category' => $category,
        ]);
    }

    /**
     * Update the specified category in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->authorizeUser($category);

        $validated = $request->validated();

        $category->update($validated);

        return Redirect::route('categories.index')
            ->with('success', 'Kategori berhasil diperbarui');
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $this->authorizeUser($category);

        if ($category->transactions()->exists()) {
            return Redirect::route('categories.index')
                ->with('error', 'Tidak dapat menghapus kategori yang memiliki transaksi. Hapus transaksi terlebih dahulu.');
        }

        $category->delete();

        return Redirect::route('categories.index')
            ->with('success', 'Kategori berhasil dihapus');
    }

    /**
     * Check if user owns the category.
     */
    private function authorizeUser(Category $category): void
    {
        if ((int) $category->user_id !== (int) auth()->id()) {
            abort(403, 'Unauthorized');
        }
    }
}
