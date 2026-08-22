<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Show the electronic library landing page.
     */
    public function index(): View
    {
        return view('home', [
            'subjects' => Category::withCount('books')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }
}
