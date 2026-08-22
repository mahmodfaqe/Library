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
        $search = $request->query('q');
        $department = $request->query('department');

        $books = Book::with('department')
            ->matching(is_string($search) ? $search : null)
            ->inDepartment(is_string($department) ? $department : null)
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
