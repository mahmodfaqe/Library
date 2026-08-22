@extends('admin.layout')

@section('title', __('admin.users.title'))

@section('content')
    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
            <h2 style="font-size:1.15rem;">{{ __('admin.users.heading', ['count' => $users->count()]) }}</h2>
        </div>

        <div class="table-scroll">
            <table>
            <thead>
                <tr>
                    <th>{{ __('admin.users.table.name') }}</th>
                    <th>{{ __('admin.users.table.email') }}</th>
                    <th>{{ __('admin.users.table.role') }}</th>
                    <th>{{ __('admin.users.table.last_login') }}</th>
                    <th>{{ __('admin.users.table.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td dir="auto">{{ $user->name }}</td>
                        <td dir="ltr">{{ $user->email }}</td>
                        <td>{{ __("admin.users.roles.{$user->role}") }}</td>
                        <td style="white-space:nowrap; font-size:0.82rem; color:#6b6b80;">
                            {{ $user->last_login_at?->format('Y-m-d H:i') ?? '—' }}
                        </td>
                        <td class="actions">
                            @if ($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" style="display:inline;"
                                      onsubmit="return confirm(@js(__('admin.users.confirm_delete')));">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">{{ __('admin.actions.delete') }}</button>
                                </form>
                            @else
                                <span style="color:#8a8aa0; font-size:0.85rem;">{{ __('admin.users.you') }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
            </div>
    </div>

    <div class="card" style="margin-top:1.5rem;">
        <h2 style="font-size:1.05rem; margin-bottom:1rem;">{{ __('admin.users.add') }}</h2>
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            <div class="field">
                <label for="name">{{ __('admin.users.table.name') }}</label>
                <input type="text" id="name" name="name" required maxlength="120" value="{{ old('name') }}">
            </div>
            <div class="field">
                <label for="email">{{ __('admin.users.table.email') }}</label>
                <input type="email" id="email" name="email" required dir="ltr" value="{{ old('email') }}">
            </div>
            <div class="field">
                <label for="password">{{ __('admin.login.password') }}</label>
                <input type="password" id="password" name="password" required minlength="12" autocomplete="new-password">
                <div class="hint">{{ __('admin.users.password_hint') }}</div>
            </div>
            <div class="field">
                <label for="role">{{ __('admin.users.table.role') }}</label>
                <select id="role" name="role" required>
                    @foreach (\App\Models\User::ROLES as $role)
                        <option value="{{ $role }}">{{ __("admin.users.roles.$role") }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">{{ __('admin.actions.create') }}</button>
        </form>
    </div>
@endsection
