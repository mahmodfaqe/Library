<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            // Long rather than cryptic: length is what actually resists guessing.
            'password' => ['required', 'string', 'min:12'],
            'role' => ['required', Rule::in(User::ROLES)],
        ]);

        $user = User::create($data);
        Activity::record('user.created', $user->email);

        return redirect()->route('admin.users')->with('status', __('admin.flash.user_created'));
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return redirect()->route('admin.users')->withErrors([
                'user' => __('admin.users.cannot_delete_self'),
            ]);
        }

        // Never leave the system without a way back in.
        if ($user->isAdmin() && User::where('role', User::ROLE_ADMIN)->count() === 1) {
            return redirect()->route('admin.users')->withErrors([
                'user' => __('admin.users.last_admin'),
            ]);
        }

        Activity::record('user.deleted', $user->email);
        $user->delete();

        return redirect()->route('admin.users')->with('status', __('admin.flash.user_deleted'));
    }

    public function activity(): View
    {
        return view('admin.activity.index', [
            'activities' => Activity::latest()->paginate(30),
        ]);
    }
}
