@use('App\Support\Locale')
<!DOCTYPE html>
<html lang="{{ Locale::htmlLang() }}" dir="{{ Locale::dir() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', __('admin.title')) — {{ __('admin.suffix') }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, -apple-system, "Segoe UI", Tahoma, sans-serif; background: #f0f2ff; color: #2d2d3a; min-height: 100vh; }
        .topbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; }
        .topbar h1 { font-size: 1.15rem; font-weight: 700; }
        .topbar a { color: #fff; text-decoration: none; font-size: 0.9rem; opacity: 0.9; }
        .topbar a:hover { opacity: 1; text-decoration: underline; }
        .container { max-width: 1100px; margin: 2rem auto; padding: 0 1rem; }
        .card { background: #fff; border: 1px solid #e5e7f5; border-radius: 14px; padding: 1.5rem; box-shadow: 0 4px 16px rgba(102,126,234,0.10); }
        .btn { display: inline-block; padding: 0.55rem 1.1rem; border-radius: 9999px; border: none; cursor: pointer; font-size: 0.9rem; font-weight: 600; text-decoration: none; transition: opacity 0.2s; }
        .btn:hover { opacity: 0.88; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; }
        .btn-danger { background: #f43f5e; color: #fff; }
        .btn-secondary { background: #e5e7f5; color: #2d2d3a; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { padding: 0.7rem 0.6rem; text-align: start; border-bottom: 1px solid #eef0f8; font-size: 0.92rem; vertical-align: middle; }
        th { color: #6b6b80; font-weight: 600; font-size: 0.82rem; }
        .actions a, .actions button { margin-inline-end: 0.4rem; }
        .alert { background: #d1fae5; color: #065f46; padding: 0.8rem 1rem; border-radius: 10px; margin-bottom: 1rem; font-size: 0.95rem; }
        .error { background: #fee2e2; color: #991b1b; padding: 0.8rem 1rem; border-radius: 10px; margin-bottom: 1rem; font-size: 0.95rem; }
        .field { margin-bottom: 1.1rem; }
        .field label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.35rem; color: #4a4a5c; }
        .field input, .field textarea, .field select { width: 100%; padding: 0.6rem 0.8rem; border: 1px solid #d5d9ee; border-radius: 10px; font-size: 0.95rem; font-family: inherit; }
        .field input:focus, .field textarea:focus, .field select:focus { outline: 2px solid #a5b4fc; border-color: transparent; }
        .field .hint { font-size: 0.78rem; color: #8a8aa0; margin-top: 0.25rem; }
        .error-msg { color: #dc2626; font-size: 0.8rem; margin-top: 0.25rem; }
        .lang-block { border: 1px dashed #c7cdf5; border-radius: 12px; padding: 1rem 1.2rem; margin-bottom: 1.2rem; background: #fafbff; }
        .lang-block h3 { font-size: 0.95rem; color: #5b5bd6; margin-bottom: 0.9rem; }
        .pagination { margin-top: 1rem; display: flex; gap: 0.4rem; justify-content: center; }
        .pagination a, .pagination span { padding: 0.4rem 0.8rem; border-radius: 8px; border: 1px solid #d5d9ee; font-size: 0.85rem; text-decoration: none; color: #2d2d3a; }
        .pagination .current { background: #667eea; color: #fff; border-color: #667eea; }
        .icon-preview { font-size: 1.6rem; vertical-align: middle; }
    </style>
</head>
<body>
    <div class="topbar">
        <div style="display:flex; align-items:center; gap:1.4rem; flex-wrap:wrap;">
            <h1>{{ __('admin.heading') }}</h1>
            <nav style="display:flex; gap:1rem; font-size:0.9rem;">
                <a href="{{ route('admin.index') }}">{{ __('admin.nav.departments') }}</a>
                <a href="{{ route('admin.feedback') }}">{{ __('admin.nav.feedback') }}</a>
            </nav>
        </div>
        <a href="{{ Locale::url() }}">{{ __('admin.back_home') }}</a>
    </div>
    <div class="container">
        @if (session('status'))
            <div class="alert">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @yield('content')
    </div>
</body>
</html>
