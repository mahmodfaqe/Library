<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        Feedback::create($data);

        return back()->with('feedback_sent', true);
    }
}
