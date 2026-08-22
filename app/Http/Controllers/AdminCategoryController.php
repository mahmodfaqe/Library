<?php

namespace App\Http\Controllers;

use App\Http\Middleware\CachePage;
use App\Models\Activity;
use App\Models\Category;
use App\Support\Locale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminCategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => Category::withCount('books')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.form', ['category' => new Category]);
    }

    public function store(Request $request): RedirectResponse
    {
        $category = Category::create($this->validated($request));
        Activity::record('category.created', $category->name);
        CachePage::flush();

        return redirect()->route('admin.categories')->with('status', __('admin.flash.category_created'));
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.form', ['category' => $category]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $category->update($this->validated($request, $category));
        Activity::record('category.updated', $category->name);
        CachePage::flush();

        return redirect()->route('admin.categories')->with('status', __('admin.flash.category_updated'));
    }

    public function destroy(Category $category): RedirectResponse
    {
        // Books outlive their subject: the foreign key nulls rather than
        // cascading, so deleting a shelf never deletes its books.
        Activity::record('category.deleted', $category->name);
        $category->delete();
        CachePage::flush();

        return redirect()->route('admin.categories')->with('status', __('admin.flash.category_deleted'));
    }

    private function validated(Request $request, ?Category $category = null): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:120', Rule::unique('categories', 'name')->ignore($category?->id)],
            'icon' => ['nullable', 'string', 'max:16'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];

        foreach (Locale::SUPPORTED as $locale) {
            $rules["translations.$locale"] = ['nullable', 'string', 'max:120'];
        }

        $data = $request->validate($rules);

        // Blank fields fall back to the Kurdish Sorani name rather than being
        // stored as empty strings.
        $data['translations'] = array_filter($data['translations'] ?? [], 'filled');

        return $data;
    }
}
