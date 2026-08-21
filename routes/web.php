<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\HomeController;
use App\Support\Locale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// The default locale lives at the root; the prefixed form is a duplicate, so
// it redirects rather than serving the same page at two addresses.
Route::redirect('/'.Locale::DEFAULT, '/', 301);

Route::get('/', [HomeController::class, 'index'])->name('home')->middleware('pagecache');

// One crawlable URL per language: /en, /ar, /ku-badini, ...
Route::get('/{locale}', [HomeController::class, 'index'])
    ->whereIn('locale', Locale::SUPPORTED)
    ->name('home.localised')
    ->middleware('pagecache');

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

    Route::middleware('admin.auth')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::get('departments/create', [AdminController::class, 'create'])->name('departments.create');
        Route::post('departments', [AdminController::class, 'store'])->name('departments.store');
        Route::get('departments/{department}/edit', [AdminController::class, 'edit'])->name('departments.edit');
        Route::put('departments/{department}', [AdminController::class, 'update'])->name('departments.update');
        Route::delete('departments/{department}', [AdminController::class, 'destroy'])->name('departments.destroy');
        Route::get('feedback', [AdminController::class, 'feedback'])->name('feedback');
        Route::delete('feedback/{feedback}', [AdminController::class, 'destroyFeedback'])->name('feedback.destroy');
    });
});
