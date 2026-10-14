<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/* Route::get('/', function () { */
/*     return view('welcome'); */
/* }); */

Route::get('/', function () {
    return Inertia::render('Sidebar/Index');
});

Route::get('/login', function () {
    return Inertia::render('Login/Index');
});
