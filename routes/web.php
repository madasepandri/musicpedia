<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SongController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\Admin\SongController as AdminSongController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ReportController as AdminReportController; // Add this line

Route::get('/', function () {
    return view('welcome');
});

Route::get('/songs', [SongController::class, 'index'])->name('songs.index');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // My Collection route
    Route::get('/my-collection', [PurchaseController::class, 'index'])->name('collection.index');

    // Purchase song route
    Route::post('/songs/{song}/purchase', [PurchaseController::class, 'store'])->name('songs.purchase');

    // Admin routes
    Route::middleware(['admin'])->group(function () {
        Route::get('/admin/dashboard', function () {
            return view('admin.dashboard');
        })->name('admin.dashboard');

        // Admin Song Management
        Route::resource('admin/songs', AdminSongController::class)->names('admin.songs');

        // Admin User Management
        Route::resource('admin/users', AdminUserController::class)->except(['create', 'store', 'show'])->names('admin.users');

        // Admin Report Export
        Route::get('/admin/reports/sales', [AdminReportController::class, 'exportSales'])->name('admin.reports.sales'); // Add this line
    });
});

require __DIR__.'/auth.php';
