<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBatchRequest;
use App\Http\Resources\BatchResource;
use App\Models\Batch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class BatchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     * 
     * @param StoreBatchRequest $request
     * @return JsonResponse
     */
    public function store(StoreBatchRequest $request): JsonResponse
    {
        try {
            // Iniciar transacción para asegurar consistencia
            DB::beginTransaction();

            // Crear el lote con los datos validados
            $batch = Batch::create([
                'producer_id' => Auth::id() ?? 1, // Usuario autenticado o usuario de prueba
                'animal_type_id' => $request->validated('animal_type_id'),
                'quantity' => $request->validated('quantity'),
                'age_months' => $request->validated('age_months'),
                'average_weight_kg' => $request->validated('average_weight_kg'),
                'suggested_price_ars' => $request->validated('suggested_price_ars'),
                'suggested_price_usd' => $request->validated('suggested_price_usd'),
                'status' => 'available', // Por defecto disponible
                'notes' => $request->validated('notes'),
            ]);

            // Cargar las relaciones para la respuesta
            $batch->load(['producer', 'animalType', 'sales']);

            // Confirmar transacción
            DB::commit();

            // Log del evento exitoso
            Log::info('Lote creado exitosamente', [
                'batch_id' => $batch->id,
                'producer_id' => Auth::id(),
                'animal_type_id' => $batch->animal_type_id
            ]);

            // Retornar respuesta exitosa con el resource
            return response()->json([
                'success' => true,
                'message' => 'Lote creado exitosamente',
                'data' => new BatchResource($batch)
            ], 201);

        } catch (Exception $e) {
            // Revertir transacción en caso de error
            DB::rollBack();

            // Log del error
            Log::error('Error al crear lote', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'request_data' => $request->validated()
            ]);

            // Retornar respuesta de error
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor al crear el lote',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
