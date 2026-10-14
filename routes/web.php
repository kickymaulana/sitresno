<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\SsoController;
use App\Http\Controllers\UserAccessController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', fn () => Inertia::render('Login/Index'))->name('login');
    Route::get('/auth/sso/redirect', [SsoController::class, 'redirect'])->name('sso.redirect');
    Route::get('/auth/sso/callback', [SsoController::class, 'callback'])->name('sso.callback');
});

Route::middleware(['auth', 'approved'])->group(function (): void {
    Route::get('/', fn () => Inertia::render('Sidebar/Index'))->name('dashboard');
    Route::post('/logout', AuthenticatedSessionController::class)->name('logout');

    Route::middleware('permission:users.view')->group(function (): void {
        Route::get('/users/access', [UserAccessController::class, 'index'])->name('users.access.index');
        Route::patch('/users/{user}/access', [UserAccessController::class, 'update'])->name('users.access.update');
    });
});
