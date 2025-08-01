<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Batch;
use App\Http\Requests\StoreSaleRequest;
use App\Http\Requests\UpdateSaleRequest;
use App\Http\Resources\SaleResource;
use App\Http\Resources\SaleCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    /**
     * Listar todas las ventas
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Sale::with(['batch.animalType', 'batch.producer']);

            // Filtros opcionales
            if ($request->has('batch_id')) {
                $query->where('batch_id', $request->batch_id);
            }

            if ($request->has('buyer_name')) {
                $query->where('buyer_name', 'like', '%' . $request->buyer_name . '%');
            }

            if ($request->has('payment_method')) {
                $query->where('payment_method', $request->payment_method);
            }

            if ($request->has('date_from')) {
                $query->whereDate('sale_date', '>=', $request->date_from);
            }

            if ($request->has('date_to')) {
                $query->whereDate('sale_date', '<=', $request->date_to);
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'sale_date');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginación
            $perPage = $request->get('per_page', 15);
            $sales = $query->paginate($perPage);

            return response()->json([
                'message' => 'Ventas obtenidas exitosamente',
                'data' => new SaleCollection($sales->items()),
                'pagination' => [
                    'current_page' => $sales->currentPage(),
                    'last_page' => $sales->lastPage(),
                    'per_page' => $sales->perPage(),
                    'total' => $sales->total(),
                    'from' => $sales->firstItem(),
                    'to' => $sales->lastItem(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar una venta específica
     */
    public function show(Sale $sale): JsonResponse
    {
        try {
            $sale->load(['batch.animalType', 'batch.producer']);

            return response()->json([
                'message' => 'Venta obtenida exitosamente',
                'data' => new SaleResource($sale)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear una nueva venta
     */
    public function store(StoreSaleRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            // Crear la venta dentro de una transacción
            $sale = DB::transaction(function () use ($validated) {
                // Obtener el lote
                $batch = Batch::findOrFail($validated['batch_id']);
                
                // Verificar que hay stock suficiente
                if (!$batch->hasAvailableStock($validated['quantity_sold'])) {
                    throw new \Exception('La cantidad vendida excede el stock disponible del lote.');
                }
                
                // Crear la venta
                $sale = Sale::create($validated);
                
                return $sale;
            });

            // Cargar las relaciones para la respuesta
            $sale->load(['batch.animalType', 'batch.producer']);

            return response()->json([
                'message' => 'Venta creada exitosamente',
                'data' => new SaleResource($sale)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar una venta existente
     */
    public function update(UpdateSaleRequest $request, Sale $sale): JsonResponse
    {
        try {
            $validated = $request->validated();

            // Actualizar la venta dentro de una transacción
            $updatedSale = DB::transaction(function () use ($validated, $sale) {
                // Si se está cambiando la cantidad vendida, verificar stock
                if (isset($validated['quantity_sold'])) {
                    $batchId = $validated['batch_id'] ?? $sale->batch_id;
                    $batch = Batch::findOrFail($batchId);
                    
                    // Calcular la cantidad adicional necesaria
                    $additionalQuantity = $validated['quantity_sold'] - $sale->quantity_sold;
                    
                    if ($additionalQuantity > 0) {
                        // Verificar que hay stock suficiente para la cantidad adicional
                        if (!$batch->hasAvailableStock($additionalQuantity)) {
                            throw new \Exception('La cantidad vendida excede el stock disponible del lote.');
                        }
                    }
                }

                // Si se está cambiando el lote, verificar stock del nuevo lote
                if (isset($validated['batch_id']) && $validated['batch_id'] != $sale->batch_id) {
                    $newBatch = Batch::findOrFail($validated['batch_id']);
                    $quantityToSell = $validated['quantity_sold'] ?? $sale->quantity_sold;
                    
                    if (!$newBatch->hasAvailableStock($quantityToSell)) {
                        throw new \Exception('La cantidad vendida excede el stock disponible del lote.');
                    }
                }

                // Actualizar la venta
                $sale->update($validated);
                
                return $sale->fresh();
            });

            // Cargar las relaciones para la respuesta
            $updatedSale->load(['batch.animalType', 'batch.producer']);

            return response()->json([
                'message' => 'Venta actualizada exitosamente',
                'data' => new SaleResource($updatedSale)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar una venta
     */
    public function destroy(Sale $sale): JsonResponse
    {
        try {
            // Eliminar la venta dentro de una transacción
            DB::transaction(function () use ($sale) {
                // Eliminar la venta (el stock se calcula automáticamente)
                $sale->delete();
            });

            return response()->json([
                'message' => 'Venta eliminada exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de ventas
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $query = Sale::query();

            // Filtros por fecha
            if ($request->has('date_from')) {
                $query->whereDate('sale_date', '>=', $request->date_from);
            }

            if ($request->has('date_to')) {
                $query->whereDate('sale_date', '<=', $request->date_to);
            }

            // Filtro por lote
            if ($request->has('batch_id')) {
                $query->where('batch_id', $request->batch_id);
            }

            // Filtro por productor (a través del lote)
            if ($request->has('producer_id')) {
                $query->whereHas('batch', function ($q) use ($request) {
                    $q->where('producer_id', $request->producer_id);
                });
            }

            $statistics = [
                'total_sales' => $query->count(),
                'total_quantity_sold' => $query->sum('quantity_sold'),
                'total_amount_ars' => $query->sum('total_amount_ars'),
                'total_amount_usd' => $query->sum('total_amount_usd'),
                'average_price_ars' => $query->avg('unit_price_ars'),
                'average_price_usd' => $query->avg('unit_price_usd'),
            ];

            // Ventas por método de pago
            $salesByPaymentMethod = $query->clone()
                ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount_ars) as total_ars'))
                ->groupBy('payment_method')
                ->get();

            $statistics['sales_by_payment_method'] = $salesByPaymentMethod;

            // Ventas por mes (últimos 12 meses)
            $salesByMonth = $query->clone()
                ->select(
                    DB::raw('strftime("%Y", sale_date) as year'),
                    DB::raw('strftime("%m", sale_date) as month'),
                    DB::raw('COUNT(*) as count'),
                    DB::raw('SUM(total_amount_ars) as total_ars'),
                    DB::raw('SUM(quantity_sold) as quantity')
                )
                ->where('sale_date', '>=', now()->subMonths(12))
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get();

            $statistics['sales_by_month'] = $salesByMonth;

            return response()->json([
                'message' => 'Estadísticas obtenidas exitosamente',
                'data' => $statistics
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 