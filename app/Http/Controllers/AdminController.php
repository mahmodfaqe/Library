<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Feedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
            'password' => ['required', 'string'],
        ]);

        $hash = config('app.admin_password_hash', '');

        if ($hash !== '' && Hash::check($credentials['password'], $hash)) {
            $request->session()->regenerate();
            $request->session()->put('admin_authenticated', true);

            return redirect()->intended(route('admin.index'));
        }

        return back()->withErrors(['password' => __('admin.login.wrong_password')])->onlyInput('password');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('admin_authenticated');
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

        Department::create($data);
        $this->clearPageCache();

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
        $this->clearPageCache();

        return redirect()->route('admin.index')->with('status', __('admin.flash.department_updated'));
    }

    public function destroy(Department $department): RedirectResponse
    {
        $department->delete();
        $this->clearPageCache();

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
        $feedback->delete();

        return redirect()->route('admin.feedback')->with('status', __('admin.flash.feedback_deleted'));
    }

    private function clearPageCache(): void
    {
        $dir = storage_path('framework/pagecache');

        if (is_dir($dir)) {
            foreach (glob($dir.'/*') ?: [] as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }
    }
}
