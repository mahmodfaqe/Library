<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Show the electronic library landing page.
     */
    public function index(): View
    {
        return view('home', [
            'departments' => Department::orderBy('sort_order')->get(),
        ]);
    }
}
