<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

// ─── Guest routes ──────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/', fn() => redirect()->route('login'));
    Route::get('/login', [LoginController::class, 'showForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
    Route::get('/register', [RegisterController::class, 'showForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.post');
});

// ─── Auth routes ───────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');
    Route::get('/tasks', fn() => view('tasks.index'))->name('tasks.index');
    Route::get('/tasks/completed', fn() => view('tasks.completed'))->name('tasks.completed');
    Route::get('/tasks/important', fn() => view('tasks.important'))->name('tasks.important');
    Route::get('/settings', fn() => view('settings'))->name('settings');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});
