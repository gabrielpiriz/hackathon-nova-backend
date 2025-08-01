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
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Construir query base con relaciones
            $query = Batch::with(['producer', 'animalType', 'sales']);
            
            // Filtros opcionales
            if ($request->has('animal_type_id') && $request->animal_type_id) {
                $query->where('animal_type_id', $request->animal_type_id);
            }
            
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }
            
            if ($request->has('producer_id') && $request->producer_id) {
                $query->where('producer_id', $request->producer_id);
            }
            
            // Búsqueda por texto en notas
            if ($request->has('search') && $request->search) {
                $query->where('notes', 'like', '%' . $request->search . '%');
            }
            
            // Ordenamiento
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            
            // Validar campos de ordenamiento permitidos
            $allowedSortFields = ['id', 'created_at', 'updated_at', 'quantity', 'age_months', 'average_weight_kg', 'suggested_price_ars', 'suggested_price_usd'];
            if (!in_array($sortBy, $allowedSortFields)) {
                $sortBy = 'created_at';
            }
            
            $query->orderBy($sortBy, $sortDirection);
            
            // Paginación
            $perPage = $request->get('per_page', 15);
            $perPage = min($perPage, 100); // Máximo 100 por página
            
            $batches = $query->paginate($perPage);
            
            // Transformar los datos usando el Resource
            $batchesCollection = $batches->getCollection()->map(function ($batch) {
                return [
                    'id' => $batch->id,
                    'lote_code' => 'Lot ' . str_pad($batch->id, 3, '0', STR_PAD_LEFT),
                    'tipo' => $batch->animalType->name,
                    'edad_promedio' => $this->formatAge($batch->age_months),
                    'peso_promedio' => number_format($batch->average_weight_kg, 0) . ' kg',
                    'precio_pesos' => '$' . number_format($batch->suggested_price_ars, 0, ',', '.'),
                    'precio_usd' => '$' . number_format($batch->suggested_price_usd, 0, ',', '.'),
                    'vendido' => $this->isSold($batch),
                    'vendido_label' => $this->isSold($batch) ? 'Si' : 'No',
                    'status' => $batch->status,
                    'status_label' => $this->getStatusLabel($batch->status),
                    'quantity' => $batch->quantity,
                    'remaining_quantity' => $batch->quantity - $batch->sales->sum('quantity_sold'),
                    'total_sold' => $batch->sales->sum('quantity_sold'),
                    'sales_count' => $batch->sales->count(),
                    'producer' => [
                        'id' => $batch->producer->id,
                        'name' => $batch->producer->full_name,
                        'email' => $batch->producer->email
                    ],
                    'animal_type' => [
                        'id' => $batch->animalType->id,
                        'name' => $batch->animalType->name,
                        'description' => $batch->animalType->description
                    ],
                    'notes' => $batch->notes,
                    'created_at' => $batch->created_at->format('Y-m-d H:i:s'),
                    'created_at_formatted' => $batch->created_at->format('d/m/Y'),
                    'updated_at' => $batch->updated_at->format('Y-m-d H:i:s'),
                    // Datos para acciones
                    'can_edit' => !$this->isSold($batch),
                    'can_delete' => !$this->isSold($batch) && $batch->sales->count() === 0,
                    'can_sell' => !$this->isSold($batch) && $batch->quantity > $batch->sales->sum('quantity_sold'),
                ];
            });
            
            // Reemplazar la colección en el objeto paginado
            $batches->setCollection($batchesCollection);
            
            // Estadísticas adicionales
            $totalBatches = Batch::count();
            $availableBatches = Batch::where('status', 'available')->count();
            $soldBatches = Batch::where('status', 'sold')->count();
            $totalAnimals = Batch::sum('quantity');
            
            return response()->json([
                'success' => true,
                'message' => 'Lotes obtenidos exitosamente',
                'data' => $batches->items(),
                'pagination' => [
                    'current_page' => $batches->currentPage(),
                    'last_page' => $batches->lastPage(),
                    'per_page' => $batches->perPage(),
                    'total' => $batches->total(),
                    'from' => $batches->firstItem(),
                    'to' => $batches->lastItem(),
                    'has_more_pages' => $batches->hasMorePages(),
                ],
                'filters' => [
                    'animal_type_id' => $request->animal_type_id,
                    'status' => $request->status,
                    'producer_id' => $request->producer_id,
                    'search' => $request->search,
                    'sort_by' => $sortBy,
                    'sort_direction' => $sortDirection,
                ],
                'statistics' => [
                    'total_batches' => $totalBatches,
                    'available_batches' => $availableBatches,
                    'sold_batches' => $soldBatches,
                    'total_animals' => $totalAnimals,
                ]
            ], 200);
            
        } catch (Exception $e) {
        
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los lotes',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno'
            ], 500);
        }
    }
    
    /**
     * Formatear edad en años y meses
     */
    private function formatAge(int $months): string
    {
        if ($months < 12) {
            return $months . ' ' . ($months === 1 ? 'mes' : 'meses');
        }
        
        $years = intval($months / 12);
        $remainingMonths = $months % 12;
        
        $ageString = $years . ' ' . ($years === 1 ? 'año' : 'años');
        
        if ($remainingMonths > 0) {
            $ageString .= ' ' . $remainingMonths . ' ' . ($remainingMonths === 1 ? 'mes' : 'meses');
        }
        
        return $ageString;
    }
    
    /**
     * Determinar si un lote está vendido
     */
    private function isSold(Batch $batch): bool
    {
        return $batch->status === 'sold' || $batch->sales->sum('quantity_sold') >= $batch->quantity;
    }
    
    /**
     * Obtener etiqueta de estado
     */
    private function getStatusLabel(string $status): string
    {
        return match($status) {
            'available' => 'Disponible',
            'sold' => 'Vendido',
            'reserved' => 'Reservado',
            default => 'Desconocido'
        };
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
