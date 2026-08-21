<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>چوونەژوورەوە — بەڕێوەبەر</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, -apple-system, "Segoe UI", Tahoma, sans-serif; background: linear-gradient(160deg, #f0f2ff 0%, #e8ebff 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1rem; }
        .box { background: #fff; border-radius: 18px; padding: 2.2rem; width: 100%; max-width: 380px; box-shadow: 0 12px 40px rgba(102,126,234,0.18); }
        h1 { font-size: 1.3rem; text-align: center; color: #2d2d3a; margin-bottom: 1.6rem; }
        .field { margin-bottom: 1.2rem; }
        .field label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.35rem; color: #4a4a5c; }
        .field input { width: 100%; padding: 0.65rem 0.85rem; border: 1px solid #d5d9ee; border-radius: 10px; font-size: 0.95rem; }
        .field input:focus { outline: 2px solid #a5b4fc; border-color: transparent; }
        .btn { width: 100%; padding: 0.7rem; border: none; border-radius: 9999px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; font-size: 0.95rem; font-weight: 700; cursor: pointer; }
        .btn:hover { opacity: 0.9; }
        .error { background: #fee2e2; color: #991b1b; padding: 0.7rem 0.9rem; border-radius: 10px; margin-bottom: 1rem; font-size: 0.88rem; }
        .back { text-align: center; margin-top: 1.1rem; font-size: 0.85rem; }
        .back a { color: #667eea; text-decoration: none; }
    </style>
</head>
<body>
    <div class="box">
        <h1>چوونەژوورەوەی بەڕێوەبەر</h1>
        @if ($errors->any())
            <div class="error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif
        <form method="POST" action="{{ route('admin.login') }}">
            @csrf
            <div class="field">
                <label for="password">وشەی نهێنی</label>
                <input type="password" id="password" name="password" required autofocus autocomplete="current-password">
            </div>
            <button type="submit" class="btn">چوونەژوورەوە</button>
        </form>
        <div class="back"><a href="{{ route('home') }}">گەڕانەوە بۆ ماڵپەڕ</a></div>
    </div>
</body>
</html>
