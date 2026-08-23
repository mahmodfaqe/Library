<?php

use App\Http\Controllers\AdminBookController;
use App\Http\Controllers\AdminCategoryController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\UserController;
use App\Support\Locale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// The default locale lives at the root; the prefixed form is a duplicate, so
// it redirects rather than serving the same page at two addresses.
Route::redirect('/'.Locale::DEFAULT, '/', 301);

Route::get('/', [HomeController::class, 'index'])->name('home')->middleware('pagecache');

// The catalogue, at the root and behind each locale prefix.
Route::get('books', [BookController::class, 'index'])->name('books');
Route::get('{locale}/books', [BookController::class, 'index'])
    ->whereIn('locale', Locale::SUPPORTED)
    ->name('books.localised');

Route::get('books/{book}/download', [BookController::class, 'download'])
    ->name('books.download');

// Suggestions for the catalogue's search box, answered as the visitor types.
// Throttled because it runs on every keystroke.
Route::get('search/suggest', [SearchController::class, 'suggest'])
    ->middleware('throttle:120,1')
    ->name('search.suggest');
Route::get('{locale}/search/suggest', [SearchController::class, 'suggest'])
    ->whereIn('locale', Locale::SUPPORTED)
    ->middleware('throttle:120,1')
    ->name('search.suggest.localised');

// One crawlable URL per language: /en, /ar, /ku-badini, ...
Route::get('/{locale}', [HomeController::class, 'index'])
    ->whereIn('locale', Locale::SUPPORTED)
    ->name('home.localised')
    ->middleware('pagecache');

// The privacy notice, at the root and behind each locale prefix, so the
// language switcher in the shared header keeps the visitor on the page.
Route::view('privacy', 'pages.privacy')->name('privacy');
Route::view('{locale}/privacy', 'pages.privacy')
    ->whereIn('locale', Locale::SUPPORTED)
    ->name('privacy.localised');

Route::get('sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// The old query-string switcher, kept so existing links keep working.
Route::get('language', function (Request $request) {
    $locale = $request->query('locale');

    return redirect(Locale::supports($locale) ? Locale::url($locale) : url('/'), 301);
})->name('language.switch');

Route::post('feedback', [FeedbackController::class, 'store'])
    ->middleware('throttle:5,10')
    ->name('feedback.store');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AdminController::class, 'showLogin'])->name('login');
    Route::post('login', [AdminController::class, 'login'])->middleware('throttle:5,1')->name('login.submit');
    Route::post('logout', [AdminController::class, 'logout'])->name('logout');

    // The panel has no localised address, so the language it is read in comes
    // from the session. This is how a staff member sets it.
    Route::get('language/{locale}', function (string $locale, Request $request) {
        $request->session()->put('locale', $locale);

        return back();
    })->whereIn('locale', Locale::SUPPORTED)->name('language');

    Route::middleware('admin.auth')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::get('departments/create', [AdminController::class, 'create'])->name('departments.create');
        Route::post('departments', [AdminController::class, 'store'])->name('departments.store');
        Route::get('departments/{department}/edit', [AdminController::class, 'edit'])->name('departments.edit');
        Route::put('departments/{department}', [AdminController::class, 'update'])->name('departments.update');
        Route::delete('departments/{department}', [AdminController::class, 'destroy'])->name('departments.destroy');
        Route::get('categories', [AdminCategoryController::class, 'index'])->name('categories');
        Route::get('categories/create', [AdminCategoryController::class, 'create'])->name('categories.create');
        Route::post('categories', [AdminCategoryController::class, 'store'])->name('categories.store');
        Route::get('categories/{category}/edit', [AdminCategoryController::class, 'edit'])->name('categories.edit');
        Route::put('categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
        Route::delete('categories/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('books', [AdminBookController::class, 'index'])->name('books');
        Route::get('books/create', [AdminBookController::class, 'create'])->name('books.create');
        Route::post('books', [AdminBookController::class, 'store'])->name('books.store');
        Route::get('books/{book}/edit', [AdminBookController::class, 'edit'])->name('books.edit');
        Route::put('books/{book}', [AdminBookController::class, 'update'])->name('books.update');
        Route::delete('books/{book}', [AdminBookController::class, 'destroy'])->name('books.destroy');

        Route::get('feedback', [AdminController::class, 'feedback'])->name('feedback');
        Route::delete('feedback/{feedback}', [AdminController::class, 'destroyFeedback'])->name('feedback.destroy');

        // Anyone signed in may manage their own account.
        Route::get('account', [UserController::class, 'account'])->name('account');
        Route::put('account', [UserController::class, 'updateAccount'])->name('account.update');
        Route::put('account/password', [UserController::class, 'updatePassword'])->name('account.password');

        // Accounts and the audit trail are for administrators only.
        Route::middleware('admin.auth:admin')->group(function () {
            Route::get('users', [UserController::class, 'index'])->name('users');
            Route::post('users', [UserController::class, 'store'])->name('users.store');
            Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
            Route::get('activity', [UserController::class, 'activity'])->name('activity');
        });
    });
});
