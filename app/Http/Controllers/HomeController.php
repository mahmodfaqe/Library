<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Models\Thesis;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Show the electronic library landing page.
     */
    public function index(): View
    {
        $subjects = Category::withCount('books')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('home', [
            'subjects' => $subjects,
            // The size of the collection, in the four numbers a reader asks
            // about first. The university heads its own page the same way.
            'facts' => [
                'books' => Book::count(),
                'subjects' => $subjects->count(),
                'theses' => Thesis::published()->count(),
                'languages' => Book::whereNotNull('language')->distinct()->count('language'),
            ],
        ]);
    }
}
