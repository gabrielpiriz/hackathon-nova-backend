<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\SaleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
|
| Rutas para autenticación de usuarios
|
*/

// Public authentication routes
Route::post('register', [AuthController::class, 'register'])->name('auth.register');
Route::post('login', [AuthController::class, 'login'])->name('auth.login');

// Protected authentication routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::get('profile', [AuthController::class, 'profile'])->name('auth.profile');
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| Batch Routes
|--------------------------------------------------------------------------
|
| Rutas para la gestión de lotes de animales
|
*/

Route::middleware('auth:sanctum')->group(function () {
    // Rutas para lotes (batches)
    Route::apiResource('batches', BatchController::class);

    // Rutas adicionales específicas para lotes si se necesitan en el futuro
    Route::prefix('batches')->group(function () {
        Route::get('my-batches', [BatchController::class, 'myBatches'])->name('batches.my');
        Route::get('{batch}/sales', [BatchController::class, 'sales'])->name('batches.sales');
        Route::patch('{batch}/status', [BatchController::class, 'updateStatus'])->name('batches.status');
        Route::post('analyze', [BatchController::class, 'analyze'])->name('batches.analyze');
    });

    // Rutas para ventas (sales)
    Route::apiResource('sales', SaleController::class);
    
    // Rutas adicionales específicas para ventas
    Route::prefix('sales')->group(function () {
        Route::get('statistics', [SaleController::class, 'statistics'])->name('sales.statistics');
    });
});

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
|
| Rutas que no requieren autenticación
|
*/

// Ruta para obtener tipos de animales (útil para formularios)
Route::get('animal-types', function () {
    return response()->json([
        'success' => true,
        'data' => \App\Models\AnimalType::select('id', 'name', 'description')->get()
    ]);
});

// Ruta de estado de la API
Route::get('status', function () {
    return response()->json([
        'success' => true,
        'message' => 'API funcionando correctamente',
        'timestamp' => now(),
        'version' => '1.0.0'
    ]);
});

/*
|--------------------------------------------------------------------------
| Testing Routes (SIN AUTENTICACIÓN - SOLO PARA DESARROLLO)
|--------------------------------------------------------------------------
*/

// Rutas temporales para testing sin autenticación
Route::post('test/batches', [BatchController::class, 'store'])->name('test.batches.store');
Route::get('test/batches', [BatchController::class, 'index'])->name('test.batches.index');
Route::delete('test/batches/{id}', [BatchController::class, 'destroy'])->name('test.batches.destroy');
Route::post('test/sales', [SaleController::class, 'store'])->name('test.sales.store');
Route::get('test/sales', [SaleController::class, 'index'])->name('test.sales.index');
Route::get('test/sales/statistics', [SaleController::class, 'statistics'])->name('test.sales.statistics');
Route::get('test/sales/{sale}', [SaleController::class, 'show'])->name('test.sales.show');
Route::put('test/sales/{sale}', [SaleController::class, 'update'])->name('test.sales.update');
Route::delete('test/sales/{sale}', [SaleController::class, 'destroy'])->name('test.sales.destroy');
Route::post('test/batches/analyze', [BatchController::class, 'analyze'])->name('test.batches.analyze');
