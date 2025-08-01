<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBatchRequest;
use App\Http\Resources\BatchResource;
use App\Models\Batch;
use App\Models\PriceHistory;
use App\Models\AnimalType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Http;

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
     * Mark a batch as sold
     * 
     * @param string $id
     * @return JsonResponse
     */
    public function markAsSold(string $id): JsonResponse
    {
        try {
            // Buscar el lote con sus relaciones
            $batch = Batch::with(['sales', 'producer', 'animalType'])->find($id);

            // Validar que el lote existe
            if (!$batch) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lote no encontrado',
                    'error' => 'El lote especificado no existe'
                ], 404);
            }

            // Verificar permisos del usuario (si está autenticado)
            if (Auth::check() && $batch->producer_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autorizado',
                    'error' => 'No tiene permisos para modificar este lote'
                ], 403);
            }

            // Validar que el lote no esté ya vendido
            if ($batch->status === 'sold') {
                return response()->json([
                    'success' => false,
                    'message' => 'El lote ya está marcado como vendido',
                    'error' => 'No se puede cambiar el estado de un lote ya vendido'
                ], 422);
            }

            // Actualizar el status en una transacción
            DB::transaction(function () use ($batch) {
                $batch->update(['status' => 'sold']);
            });

            // Log del evento exitoso
            Log::info('Lote marcado como vendido', [
                'batch_id' => $batch->id,
                'producer_id' => $batch->producer_id,
                'previous_status' => $batch->getOriginal('status'),
                'updated_by_user_id' => Auth::id(),
                'timestamp' => now()
            ]);

            // Recargar el lote con relaciones actualizadas
            $batch->refresh();
            $batch->load(['producer', 'animalType', 'sales']);

            // Retornar respuesta exitosa
            return response()->json([
                'success' => true,
                'message' => 'Lote marcado como vendido exitosamente',
                'data' => [
                    'batch_id' => $batch->id,
                    'previous_status' => $batch->getOriginal('status'),
                    'new_status' => 'sold',
                    'updated_at' => $batch->updated_at->format('Y-m-d H:i:s'),
                    'batch_info' => [
                        'code' => 'Lot ' . str_pad($batch->id, 3, '0', STR_PAD_LEFT),
                        'animal_type' => $batch->animalType->name,
                        'quantity' => $batch->quantity,
                        'producer' => $batch->producer->full_name
                    ]
                ]
            ], 200);

        } catch (Exception $e) {
            // Log del error
            Log::error('Error al marcar lote como vendido', [
                'batch_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Retornar respuesta de error
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor al marcar el lote como vendido',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            // Buscar el lote con sus relaciones
            $batch = Batch::with(['sales', 'priceHistories', 'producer', 'animalType'])->find($id);

            // Validar que el lote existe
            if (!$batch) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lote no encontrado',
                    'error' => 'El lote especificado no existe'
                ], 404);
            }

            // Validaciones de negocio
            $canDelete = $this->validateCanDelete($batch);
            if (!$canDelete['can_delete']) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el lote',
                    'error' => $canDelete['reason'],
                    'restrictions' => $canDelete['restrictions']
                ], 422);
            }

            // Verificar permisos del usuario (si está autenticado)
            if (Auth::check() && $batch->producer_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autorizado',
                    'error' => 'No tiene permisos para eliminar este lote'
                ], 403);
            }

            // Guardar información para el log antes de eliminar
            $batchInfo = [
                'id' => $batch->id,
                'producer_id' => $batch->producer_id,
                'animal_type' => $batch->animalType->name,
                'quantity' => $batch->quantity,
                'status' => $batch->status,
                'created_at' => $batch->created_at,
            ];

            // Ejecutar eliminación en transacción
            $result = DB::transaction(function () use ($batch) {
                // 1. Eliminar historial de precios relacionado
                if ($batch->priceHistories->count() > 0) {
                    $batch->priceHistories()->delete();
                }

                // 2. Eliminar el lote
                $batch->delete();
                
                return true;
            });

            // Log del evento exitoso
            Log::info('Lote eliminado exitosamente', [
                'deleted_batch' => $batchInfo,
                'deleted_by_user_id' => Auth::id(),
                'timestamp' => now()
            ]);

            // Retornar respuesta exitosa
            return response()->json([
                'success' => true,
                'message' => 'Lote eliminado exitosamente',
                'data' => [
                    'deleted_batch_id' => $batchInfo['id'],
                    'deleted_at' => now()->format('Y-m-d H:i:s'),
                    'batch_info' => [
                        'code' => 'Lot ' . str_pad($batchInfo['id'], 3, '0', STR_PAD_LEFT),
                        'animal_type' => $batchInfo['animal_type'],
                        'quantity' => $batchInfo['quantity'],
                    ]
                ]
            ], 200);

        } catch (Exception $e) {
            // Log del error
            Log::error('Error al eliminar lote', [
                'batch_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Retornar respuesta de error
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor al eliminar el lote',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno'
            ], 500);
        }
    }

    /**
     * Validar si un lote se puede eliminar
     * 
     * @param Batch $batch
     * @return array
     */
    private function validateCanDelete(Batch $batch): array
    {
        $restrictions = [];
        
        // No se puede eliminar si tiene ventas
        if ($batch->sales->count() > 0) {
            $restrictions[] = [
                'type' => 'sales_exist',
                'message' => 'El lote tiene ventas registradas',
                'count' => $batch->sales->count(),
                'total_sold' => $batch->sales->sum('quantity_sold')
            ];
        }

        // No se puede eliminar si está vendido completamente
        if ($batch->status === 'sold') {
            $restrictions[] = [
                'type' => 'status_sold',
                'message' => 'El lote está marcado como vendido',
                'status' => $batch->status
            ];
        }

        // No se puede eliminar si está reservado y tiene precio histórico reciente
        if ($batch->status === 'reserved' && $batch->priceHistories->count() > 0) {
            $recentPriceHistory = $batch->priceHistories()
                ->where('created_at', '>=', now()->subDays(7))
                ->count();
            
            if ($recentPriceHistory > 0) {
                $restrictions[] = [
                    'type' => 'reserved_with_recent_activity',
                    'message' => 'El lote está reservado con actividad reciente',
                    'recent_activity_count' => $recentPriceHistory
                ];
            }
        }

        // Resultado de validación
        $canDelete = empty($restrictions);
        
        return [
            'can_delete' => $canDelete,
            'reason' => $canDelete ? null : 'El lote no puede ser eliminado debido a restricciones de negocio',
            'restrictions' => $restrictions
        ];
    }

    /**
     * Analyze price histories for all batches and get suggested prices
     *
     * @return JsonResponse
     */
    public function analyze(): JsonResponse
    {
        try {
            // Get all batches with their price histories and animal types
            $batches = Batch::with(['priceHistories', 'animalType'])->get();

            $priceHistoriesPayload = [];

            foreach ($batches as $batch) {
                $priceHistory = [];

                // Get price history for this batch, ordered by date
                $histories = $batch->priceHistories()->orderBy('date')->get();

                foreach ($histories as $history) {
                    $priceHistory[] = [
                        'date' => $history->date->format('Y-m-d'),
                        'price' => (float) $history->price_usd
                    ];
                }

                if (!empty($priceHistory)) {
                    $priceHistoriesPayload[] = [
                        'batch_id' => 'B' . str_pad($batch->id, 3, '0', STR_PAD_LEFT),
                        'animal_type' => strtolower($batch->animalType->name),
                        'price_history' => $priceHistory
                    ];
                }
            }

            // If no price histories found, return empty response
            if (empty($priceHistoriesPayload)) {
                return response()->json([
                    'success' => true,
                    'message' => 'No price histories found for analysis',
                    'suggested_prices' => []
                ]);
            }

            // Prepare payload for external service
            $payload = [
                'price_histories' => $priceHistoriesPayload
            ];

            // Get service configuration
            $serviceUrl = config('services.price_analysis.url');
            $timeout = config('services.price_analysis.timeout', 30);

            if (!$serviceUrl) {
                throw new Exception('Price analysis service URL not configured');
            }

            // Make HTTP request to external service
            $response = Http::timeout($timeout)->post($serviceUrl, $payload);

            if (!$response->successful()) {
                throw new Exception('External service request failed: ' . $response->status());
            }

            $suggestedPrices = $response->json('suggested_prices', []);

            // Update batches with suggested prices
            DB::beginTransaction();

            $updatedBatches = [];
            $exchangeRate = 40; // Hardcoded rate: 1 USD = 40 ARS

            foreach ($suggestedPrices as $suggestedPrice) {
                // Extract batch ID from the format "B001"
                $batchId = (int) substr($suggestedPrice['batch_id'], 1);

                $batch = Batch::find($batchId);
                if ($batch) {
                    $batch->update([
                        'suggested_price_usd' => $suggestedPrice['suggested_price'],
                        'suggested_price_ars' => $suggestedPrice['suggested_price'] * $exchangeRate
                    ]);

                    $updatedBatches[] = $batch;
                }
            }

            DB::commit();

            // Log successful analysis
            Log::info('Price analysis completed successfully', [
                'batches_analyzed' => count($priceHistoriesPayload),
                'batches_updated' => count($updatedBatches)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Price analysis completed successfully',
                'suggested_prices' => $suggestedPrices
            ]);

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error during price analysis', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error during price analysis',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
