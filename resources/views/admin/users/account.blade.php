@extends('admin.layout')

@section('title', __('admin.account.title'))

@section('content')
    <div class="card">
        <h2 style="font-size:1.15rem; margin-bottom:0.3rem;">{{ __('admin.account.heading') }}</h2>
        <p style="color:#6b6b80; font-size:0.88rem; margin-bottom:1.2rem;">{{ __('admin.account.blurb') }}</p>

        <form method="POST" action="{{ route('admin.account.update') }}">
            @csrf
            @method('PUT')

            <div class="field">
                <label for="name">{{ __('admin.users.table.name') }}</label>
                <input type="text" id="name" name="name" required maxlength="120"
                       value="{{ old('name', auth()->user()->name) }}">
            </div>

            <div class="field">
                <label for="email">{{ __('admin.users.table.email') }}</label>
                <input type="email" id="email" name="email" required dir="ltr"
                       value="{{ old('email', auth()->user()->email) }}">
            </div>

            <button type="submit" class="btn btn-primary">{{ __('admin.actions.save') }}</button>
        </form>
    </div>

    <div class="card" style="margin-top:1.5rem;">
        <h2 style="font-size:1.05rem; margin-bottom:1rem;">{{ __('admin.account.password_heading') }}</h2>

        <form method="POST" action="{{ route('admin.account.password') }}">
            @csrf
            @method('PUT')

            <div class="field">
                <label for="current_password">{{ __('admin.account.current_password') }}</label>
                <input type="password" id="current_password" name="current_password" required
                       autocomplete="current-password">
            </div>

            <div class="field">
                <label for="password">{{ __('admin.account.new_password') }}</label>
                <input type="password" id="password" name="password" required minlength="12"
                       autocomplete="new-password">
                <div class="hint">{{ __('admin.users.password_hint') }}</div>
            </div>

            <div class="field">
                <label for="password_confirmation">{{ __('admin.account.confirm_password') }}</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required
                       autocomplete="new-password">
            </div>

            <button type="submit" class="btn btn-primary">{{ __('admin.account.change_password') }}</button>
        </form>
    </div>
@endsection
