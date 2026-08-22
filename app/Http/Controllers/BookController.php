<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookController extends Controller
{
    /**
     * The public catalogue: search by title or author, narrow by department.
     */
    public function index(Request $request): View
    {
        // Query strings are attacker-controlled and can arrive as arrays
        // (?q[]=x), so normalise to a string before anything else touches them.
        $search = $request->str('q')->trim()->value() ?: null;
        $department = $request->str('department')->trim()->value() ?: null;

        $books = Book::with('department')
            ->matching($search)
            ->inDepartment($department)
            ->orderBy('title')
            ->paginate(24)
            ->withQueryString();

        return view('books.index', [
            'books' => $books,
            'departments' => Department::orderBy('sort_order')->get(),
            'search' => $search,
            'department' => $department,
        ]);
    }
}
