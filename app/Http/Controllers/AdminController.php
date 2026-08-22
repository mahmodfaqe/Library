<?php

namespace App\Http\Controllers;

use App\Http\Middleware\CachePage;
use App\Models\Activity;
use App\Models\Department;
use App\Models\Feedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function showLogin(): View
    {
        return view('admin.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            Auth::user()->forceFill(['last_login_at' => now()])->save();
            Activity::record('auth.signed_in');

            return redirect()->intended(route('admin.index'));
        }

        Activity::record('auth.failed', $credentials['email']);

        return back()
            ->withErrors(['email' => __('admin.login.wrong_password')])
            ->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        Activity::record('auth.signed_out');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function index(): View
    {
        return view('admin.departments.index', [
            'departments' => Department::orderBy('sort_order')->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('admin.departments.form', [
            'department' => new Department,
            'locales' => config('departments.locales'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $department = Department::create($data);
        Activity::record('department.created', $department->translation('en', 'title'));
        CachePage::flush();

        return redirect()->route('admin.index')->with('status', __('admin.flash.department_created'));
    }

    public function edit(Department $department): View
    {
        return view('admin.departments.form', [
            'department' => $department,
            'locales' => config('departments.locales'),
        ]);
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $data = $this->validated($request);

        $department->update($data);
        Activity::record('department.updated', $department->translation('en', 'title'));
        CachePage::flush();

        return redirect()->route('admin.index')->with('status', __('admin.flash.department_updated'));
    }

    public function destroy(Department $department): RedirectResponse
    {
        Activity::record('department.deleted', $department->translation('en', 'title'));
        $department->delete();
        CachePage::flush();

        return redirect()->route('admin.index')->with('status', __('admin.flash.department_deleted'));
    }

    private function validated(Request $request): array
    {
        $rules = [
            'icon' => ['required', 'string', 'max:16'],
            'drive_url' => ['required', 'url', 'max:500'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];

        foreach (config('departments.locales') as $locale) {
            $lang = $locale['lang'];
            $rules["translations.$lang.title"] = ['required', 'string', 'max:255'];
            $rules["translations.$lang.desc"] = ['required', 'string', 'max:1000'];
            $rules["translations.$lang.button"] = ['required', 'string', 'max:120'];
        }

        return $request->validate($rules);
    }

    public function feedback(): View
    {
        return view('admin.feedback.index', [
            'feedback' => Feedback::latest()->paginate(15),
        ]);
    }

    public function destroyFeedback(Feedback $feedback): RedirectResponse
    {
        Activity::record('feedback.deleted', '#'.$feedback->id);
        $feedback->delete();

        return redirect()->route('admin.feedback')->with('status', __('admin.flash.feedback_deleted'));
    }
}
