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

    /**
     * The signed-in user's own name, email and password. Anyone with an
     * account may change their own; only administrators manage other people's.
     */
    public function account(): View
    {
        return view('admin.users.account');
    }

    public function updateAccount(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->update($data);
        Activity::record('account.updated', $user->email);

        return redirect()->route('admin.account')->with('status', __('admin.flash.account_updated'));
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        $request->validate([
            // Knowing the current one stops a walked-away session being used
            // to lock the real owner out.
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ]);

        $user->update(['password' => $request->string('password')->value()]);
        Activity::record('account.password_changed', $user->email);

        return redirect()->route('admin.account')->with('status', __('admin.flash.password_changed'));
    }

    public function activity(): View
    {
        return view('admin.activity.index', [
            'activities' => Activity::latest()->paginate(30),
        ]);
    }
}
