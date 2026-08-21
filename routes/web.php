<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home')->middleware('pagecache');

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
    });
});
