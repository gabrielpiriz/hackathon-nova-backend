<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Redirect home to dashboard
Route::get('/', function () {
    return redirect('/dashboard');
});

// Auth routes (public)
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

// Dashboard routes (protected)
Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

// Lotes routes
Route::prefix('lotes')->name('lotes.')->group(function () {
    Route::get('/', function () {
        return view('lotes.index');
    })->name('index');
    
    Route::get('/nuevo', function () {
        return view('lotes.create');
    })->name('create');
});

// Ventas routes
Route::prefix('ventas')->name('ventas.')->group(function () {
    Route::get('/', function () {
        return view('ventas.index');
    })->name('index');
});

// Análisis routes
Route::prefix('analisis')->name('analisis.')->group(function () {
    Route::get('/', function () {
        return view('analisis.index');
    })->name('index');
});
