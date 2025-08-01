<?php

use App\Http\Controllers\BatchController;
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