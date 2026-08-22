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
        $search = $this->textQuery($request, 'q');
        $department = $this->textQuery($request, 'department');

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

    /**
     * A query-string value as a string, or null.
     *
     * Query parameters are attacker-controlled and can arrive as arrays
     * (?q[]=x), which would otherwise reach the view and blow up on
     * "Array to string conversion".
     */
    private function textQuery(Request $request, string $key): ?string
    {
        $value = $request->query($key);

        if (! is_string($value)) {
            return null;
        }

        return trim($value) === '' ? null : trim($value);
    }
}
