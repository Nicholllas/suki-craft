<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', ['categories' => Category::query()->withCount('products')->latest()->paginate(15)]);
    }

    public function create(): View
    {
        return view('admin.categories.create');
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = $this->uniqueSlug($data['name']);

        Category::create($data);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = $category->name === $data['name'] ? $category->slug : $this->uniqueSlug($data['name'], $category);

        $category->update($data);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function toggle(Category $category): RedirectResponse
    {
        $category->update(['is_active' => ! $category->is_active]);

        return back()->with('success', 'Status kategori berhasil diperbarui.');
    }

    private function uniqueSlug(string $value, ?Category $category = null): string
    {
        $baseSlug = Str::slug($value) ?: 'kategori';
        $slug = $baseSlug;
        $number = 2;

        while (Category::query()->where('slug', $slug)->when($category, fn ($query) => $query->whereKeyNot($category))->exists()) {
            $slug = "{$baseSlug}-{$number}";
            $number++;
        }

        return $slug;
    }
}
