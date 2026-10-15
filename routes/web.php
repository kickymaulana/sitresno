<?php

use App\Http\Controllers\AreaController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\SsoController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\FormPengukuranController;
use App\Http\Controllers\MasterMouldController;
use App\Http\Controllers\ModelSizeController;
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

    foreach ([
        'areas' => AreaController::class,
        'master-moulds' => MasterMouldController::class,
        'model-sizes' => ModelSizeController::class,
        'customers' => CustomerController::class,
    ] as $resource => $controller) {
        Route::resource($resource, $controller)->only(['index'])->middleware('permission:masters.view');
        Route::resource($resource, $controller)->only(['create'])->middleware('permission:masters.create');
        Route::resource($resource, $controller)->only(['store']);
        Route::resource($resource, $controller)->only(['edit'])->middleware('permission:masters.update');
        Route::resource($resource, $controller)->only(['update']);
        Route::resource($resource, $controller)->only(['destroy'])->middleware('permission:masters.delete');
    }

    Route::resource('form-pengukurans', FormPengukuranController::class)->only(['create'])->middleware('permission:records.create');
    Route::resource('form-pengukurans', FormPengukuranController::class)->only(['index', 'show'])->middleware('permission:records.view');
    Route::resource('form-pengukurans', FormPengukuranController::class)->only(['store']);
    Route::resource('form-pengukurans', FormPengukuranController::class)->only(['edit'])->middleware('permission:records.update');
    Route::resource('form-pengukurans', FormPengukuranController::class)->only(['update']);
    Route::resource('form-pengukurans', FormPengukuranController::class)->only(['destroy'])->middleware('permission:records.update');
});
