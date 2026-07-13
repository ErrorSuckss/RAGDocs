<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [FileController::class, 'index']);
Route::get('/documents', [FileController::class, 'document']);
Route::get('/chat', [ChatController::class, 'index']);
Route::post('/query', [ChatController::class, 'query']);

Route::middleware('throttle:6,1')->post('/upload', [FileController::class, 'store']);
Route::delete('/delete/{file}', [FileController::class, 'destroy']);

Route::post('/python', [FileController::class,'python']);

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
