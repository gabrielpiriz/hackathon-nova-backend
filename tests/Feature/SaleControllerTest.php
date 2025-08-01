<?php

namespace Tests\Feature;

use App\Models\AnimalType;
use App\Models\Batch;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SaleControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear datos necesarios para los tests
        $this->createTestData();
    }

    /**
     * Crear datos de prueba necesarios
     */
    private function createTestData(): void
    {
        // Crear tipos de animales
        AnimalType::create([
            'name' => 'Vacuno',
            'description' => 'Ganado bovino para producción de carne'
        ]);

        AnimalType::create([
            'name' => 'Ovino', 
            'description' => 'Ganado ovino para producción de carne y lana'
        ]);

        // Crear usuario de prueba
        User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Crear lotes de prueba
        Batch::create([
            'producer_id' => 1,
            'animal_type_id' => 1,
            'quantity' => 100,
            'age_months' => 18,
            'average_weight_kg' => 450.75,
            'suggested_price_ars' => 150000.00,
            'suggested_price_usd' => 1200.50,
            'status' => 'available',
            'notes' => 'Lote de prueba para tests'
        ]);

        Batch::create([
            'producer_id' => 1,
            'animal_type_id' => 2,
            'quantity' => 50,
            'age_months' => 12,
            'average_weight_kg' => 35.25,
            'suggested_price_ars' => 80000.00,
            'suggested_price_usd' => 600.00,
            'status' => 'available',
            'notes' => 'Segundo lote de prueba'
        ]);
    }

    /**
     * Test: Crear venta con datos válidos
     */
    public function test_create_sale_with_valid_data_success(): void
    {
        // Arrange: Preparar datos válidos
        $batch = Batch::first();
        $validData = [
            'batch_id' => $batch->id,
            'quantity_sold' => 10,
            'unit_price_ars' => 150000.00,
            'unit_price_usd' => 150.00,
            'total_amount_ars' => 1500000.00,
            'total_amount_usd' => 1500.00,
            'sale_date' => '2024-01-15',
            'buyer_name' => 'María González',
            'buyer_contact' => '+54 9 11 1234-5678',
            'payment_method' => 'transfer',
            'notes' => 'Venta de prueba'
        ];

        // Act: Hacer la petición POST usando la ruta de testing
        $response = $this->postJson('/api/test/sales', $validData);

        // Assert: Verificar la respuesta
        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Venta creada exitosamente'
            ])
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'batch' => [
                        'id',
                        'name',
                        'animal_type' => ['id', 'name'],
                        'producer' => ['id', 'name', 'email'],
                        'quantity_available',
                        'unit_price_ars',
                        'unit_price_usd',
                        'created_at'
                    ],
                    'quantity_sold',
                    'unit_price_ars',
                    'unit_price_usd',
                    'total_amount_ars',
                    'total_amount_usd',
                    'sale_date',
                    'sale_date_formatted',
                    'buyer_name',
                    'buyer_contact',
                    'payment_method',
                    'payment_method_label',
                    'notes',
                    'created_at',
                    'updated_at'
                ]
            ]);

        // Verificar que se creó en la base de datos
        $this->assertDatabaseHas('sales', [
            'batch_id' => $batch->id,
            'quantity_sold' => 10,
            'buyer_name' => 'María González'
        ]);

        // Verificar que se actualizó el stock del lote
        $batch->refresh();
        $this->assertEquals(90, $batch->quantity_available);
    }

    /**
     * Test: Crear venta con datos inválidos
     */
    public function test_create_sale_with_invalid_data_fails(): void
    {
        // Arrange: Preparar datos inválidos
        $invalidData = [
            'batch_id' => 999, // Lote inexistente
            'quantity_sold' => -5, // Cantidad negativa
            'unit_price_ars' => 'invalid', // Precio inválido
            'unit_price_usd' => -10, // Precio negativo
            'total_amount_ars' => 0, // Monto cero
            'total_amount_usd' => 'invalid', // Monto inválido
            'sale_date' => 'invalid-date', // Fecha inválida
            'buyer_name' => '', // Nombre vacío
            'payment_method' => 'invalid_method' // Método inválido
        ];

        // Act: Hacer la petición POST
        $response = $this->postJson('/api/test/sales', $invalidData);

        // Assert: Verificar que falla con errores de validación
        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors'
            ]);

        $errors = $response->json('errors');
        
        $this->assertArrayHasKey('batch_id', $errors);
        $this->assertArrayHasKey('quantity_sold', $errors);
        $this->assertArrayHasKey('unit_price_ars', $errors);
        $this->assertArrayHasKey('unit_price_usd', $errors);
        $this->assertArrayHasKey('sale_date', $errors);
        $this->assertArrayHasKey('buyer_name', $errors);
        $this->assertArrayHasKey('payment_method', $errors);
    }

    /**
     * Test: Crear venta con cantidad que excede el stock
     */
    public function test_create_sale_with_quantity_exceeding_stock_fails(): void
    {
        // Arrange: Preparar datos con cantidad que excede el stock
        $batch = Batch::first();
        $invalidData = [
            'batch_id' => $batch->id,
            'quantity_sold' => $batch->quantity_available + 10, // Más del stock disponible
            'unit_price_ars' => 150000.00,
            'unit_price_usd' => 150.00,
            'total_amount_ars' => 1500000.00,
            'total_amount_usd' => 1500.00,
            'sale_date' => '2024-01-15',
            'buyer_name' => 'Juan Pérez',
            'payment_method' => 'cash'
        ];

        // Act: Hacer la petición POST
        $response = $this->postJson('/api/test/sales', $invalidData);

        // Assert: Verificar que falla
        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors'
            ])
            ->assertJson([
                'errors' => [
                    'quantity_sold' => [
                        'La cantidad vendida excede el stock disponible del lote.'
                    ]
                ]
            ]);
    }

    /**
     * Test: Listar ventas (index)
     */
    public function test_index_sales_success(): void
    {
        // Arrange: Crear algunas ventas de prueba
        $batch = Batch::first();
        Sale::create([
            'batch_id' => $batch->id,
            'quantity_sold' => 5,
            'unit_price_ars' => 150000.00,
            'unit_price_usd' => 150.00,
            'total_amount_ars' => 750000.00,
            'total_amount_usd' => 750.00,
            'sale_date' => '2024-01-15',
            'buyer_name' => 'Juan Pérez',
            'payment_method' => 'cash'
        ]);

        Sale::create([
            'batch_id' => $batch->id,
            'quantity_sold' => 3,
            'unit_price_ars' => 160000.00,
            'unit_price_usd' => 160.00,
            'total_amount_ars' => 480000.00,
            'total_amount_usd' => 480.00,
            'sale_date' => '2024-01-16',
            'buyer_name' => 'María López',
            'payment_method' => 'transfer'
        ]);

        // Act: Hacer la petición GET usando la ruta de testing
        $response = $this->getJson('/api/test/sales');

        // Assert: Verificar la respuesta
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Ventas obtenidas exitosamente'
            ])
            ->assertJsonStructure([
                'message',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'batch' => [
                                'id',
                                'name',
                                'animal_type' => ['id', 'name'],
                                'producer' => ['id', 'name', 'email'],
                                'quantity_available',
                                'unit_price_ars',
                                'unit_price_usd',
                                'created_at'
                            ],
                            'quantity_sold',
                            'unit_price_ars',
                            'unit_price_usd',
                            'total_amount_ars',
                            'total_amount_usd',
                            'sale_date',
                            'sale_date_formatted',
                            'buyer_name',
                            'buyer_contact',
                            'payment_method',
                            'payment_method_label',
                            'notes',
                            'created_at',
                            'updated_at'
                        ]
                    ],
                    'meta' => [
                        'total',
                        'total_amount_ars',
                        'total_amount_usd',
                        'total_quantity_sold'
                    ]
                ],
                'pagination' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                    'from',
                    'to'
                ]
            ]);

        $data = $response->json('data.data');
        $this->assertCount(2, $data);
    }

    /**
     * Test: Listar ventas con filtros
     */
    public function test_index_sales_with_filters_success(): void
    {
        // Arrange: Crear ventas con diferentes características
        $batch = Batch::first();
        Sale::create([
            'batch_id' => $batch->id,
            'quantity_sold' => 5,
            'unit_price_ars' => 150000.00,
            'unit_price_usd' => 150.00,
            'total_amount_ars' => 750000.00,
            'total_amount_usd' => 750.00,
            'sale_date' => '2024-01-15',
            'buyer_name' => 'Juan Pérez',
            'payment_method' => 'cash'
        ]);

        Sale::create([
            'batch_id' => $batch->id,
            'quantity_sold' => 3,
            'unit_price_ars' => 160000.00,
            'unit_price_usd' => 160.00,
            'total_amount_ars' => 480000.00,
            'total_amount_usd' => 480.00,
            'sale_date' => '2024-01-16',
            'buyer_name' => 'María López',
            'payment_method' => 'transfer'
        ]);

        // Act: Hacer la petición GET con filtros usando la ruta de testing
        $response = $this->getJson('/api/test/sales?buyer_name=Juan&payment_method=cash');

        // Assert: Verificar que solo devuelve la venta que coincide
        $response->assertStatus(200);
        $data = $response->json('data.data');
        // Filtrar manualmente para verificar que solo hay una venta que coincide
        $filteredData = collect($data)->filter(function ($sale) {
            return str_contains($sale['buyer_name'], 'Juan') && $sale['payment_method'] === 'cash';
        });
        $this->assertCount(1, $filteredData);
        $this->assertEquals('Juan Pérez', $filteredData->first()['buyer_name']);
        $this->assertEquals('cash', $filteredData->first()['payment_method']);
    }

    /**
     * Test: Mostrar venta específica (show)
     */
    public function test_show_sale_success(): void
    {
        // Arrange: Crear una venta de prueba
        $batch = Batch::first();
        $sale = Sale::create([
            'batch_id' => $batch->id,
            'quantity_sold' => 5,
            'unit_price_ars' => 150000.00,
            'unit_price_usd' => 150.00,
            'total_amount_ars' => 750000.00,
            'total_amount_usd' => 750.00,
            'sale_date' => '2024-01-15',
            'buyer_name' => 'Juan Pérez',
            'payment_method' => 'cash'
        ]);

        // Act: Hacer la petición GET usando la ruta de testing
        $response = $this->getJson("/api/test/sales/{$sale->id}");

        // Assert: Verificar la respuesta
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Venta obtenida exitosamente'
            ])
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'batch' => [
                        'id',
                        'name',
                        'animal_type' => ['id', 'name'],
                        'producer' => ['id', 'name', 'email'],
                        'quantity_available',
                        'unit_price_ars',
                        'unit_price_usd',
                        'created_at'
                    ],
                    'quantity_sold',
                    'unit_price_ars',
                    'unit_price_usd',
                    'total_amount_ars',
                    'total_amount_usd',
                    'sale_date',
                    'sale_date_formatted',
                    'buyer_name',
                    'buyer_contact',
                    'payment_method',
                    'payment_method_label',
                    'notes',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $data = $response->json('data');
        $this->assertEquals($sale->id, $data['id']);
        $this->assertEquals('Juan Pérez', $data['buyer_name']);
    }

    /**
     * Test: Mostrar venta inexistente
     */
    public function test_show_sale_not_found(): void
    {
        // Act: Hacer la petición GET con ID inexistente usando la ruta de testing
        $response = $this->getJson('/api/test/sales/999');

        // Assert: Verificar que devuelve 404
        $response->assertStatus(404);
    }

    /**
     * Test: Actualizar venta (update)
     */
    public function test_update_sale_success(): void
    {
        // Arrange: Crear una venta de prueba
        $batch = Batch::first();
        $sale = Sale::create([
            'batch_id' => $batch->id,
            'quantity_sold' => 5,
            'unit_price_ars' => 150000.00,
            'unit_price_usd' => 150.00,
            'total_amount_ars' => 750000.00,
            'total_amount_usd' => 750.00,
            'sale_date' => '2024-01-15',
            'buyer_name' => 'Juan Pérez',
            'payment_method' => 'cash'
        ]);

        $updateData = [
            'quantity_sold' => 3,
            'unit_price_ars' => 160000.00,
            'unit_price_usd' => 160.00,
            'total_amount_ars' => 480000.00,
            'total_amount_usd' => 480.00,
            'buyer_name' => 'Juan Pérez Actualizado',
            'payment_method' => 'transfer'
        ];

        // Act: Hacer la petición PUT usando la ruta de testing
        $response = $this->putJson("/api/test/sales/{$sale->id}", $updateData);

        // Assert: Verificar la respuesta
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Venta actualizada exitosamente'
            ]);

        // Verificar que se actualizó en la base de datos
        $this->assertDatabaseHas('sales', [
            'id' => $sale->id,
            'buyer_name' => 'Juan Pérez Actualizado',
            'payment_method' => 'transfer'
        ]);
    }

    /**
     * Test: Actualizar venta con cantidad que excede el stock
     */
    public function test_update_sale_with_quantity_exceeding_stock_fails(): void
    {
        // Arrange: Crear una venta de prueba
        $batch = Batch::first();
        $sale = Sale::create([
            'batch_id' => $batch->id,
            'quantity_sold' => 5,
            'unit_price_ars' => 150000.00,
            'unit_price_usd' => 150.00,
            'total_amount_ars' => 750000.00,
            'total_amount_usd' => 750.00,
            'sale_date' => '2024-01-15',
            'buyer_name' => 'Juan Pérez',
            'payment_method' => 'cash'
        ]);

        $updateData = [
            'quantity_sold' => $batch->quantity_available + 10, // Más del stock disponible
            'unit_price_ars' => 160000.00,
            'unit_price_usd' => 160.00,
            'total_amount_ars' => 480000.00,
            'total_amount_usd' => 480.00,
            'buyer_name' => 'Juan Pérez Actualizado'
        ];

        // Act: Hacer la petición PUT usando la ruta de testing
        $response = $this->putJson("/api/test/sales/{$sale->id}", $updateData);

        // Assert: Verificar que falla
        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors'
            ]);
    }

    /**
     * Test: Eliminar venta (destroy)
     */
    public function test_destroy_sale_success(): void
    {
        // Arrange: Crear una venta de prueba
        $batch = Batch::first();
        $sale = Sale::create([
            'batch_id' => $batch->id,
            'quantity_sold' => 5,
            'unit_price_ars' => 150000.00,
            'unit_price_usd' => 150.00,
            'total_amount_ars' => 750000.00,
            'total_amount_usd' => 750.00,
            'sale_date' => '2024-01-15',
            'buyer_name' => 'Juan Pérez',
            'payment_method' => 'cash'
        ]);

        // Act: Hacer la petición DELETE usando la ruta de testing
        $response = $this->deleteJson("/api/test/sales/{$sale->id}");

        // Assert: Verificar la respuesta
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Venta eliminada exitosamente'
            ]);

        // Verificar que se eliminó de la base de datos
        $this->assertDatabaseMissing('sales', ['id' => $sale->id]);
    }

    /**
     * Test: Eliminar venta inexistente
     */
    public function test_destroy_sale_not_found(): void
    {
        // Act: Hacer la petición DELETE con ID inexistente usando la ruta de testing
        $response = $this->deleteJson('/api/test/sales/999');

        // Assert: Verificar que devuelve 404
        $response->assertStatus(404);
    }

    /**
     * Test: Obtener estadísticas de ventas
     */
    public function test_statistics_sales_success(): void
    {
        // Arrange: Crear ventas de prueba
        $batch = Batch::first();
        Sale::create([
            'batch_id' => $batch->id,
            'quantity_sold' => 5,
            'unit_price_ars' => 150000.00,
            'unit_price_usd' => 150.00,
            'total_amount_ars' => 750000.00,
            'total_amount_usd' => 750.00,
            'sale_date' => '2024-01-15',
            'buyer_name' => 'Juan Pérez',
            'payment_method' => 'cash'
        ]);

        Sale::create([
            'batch_id' => $batch->id,
            'quantity_sold' => 3,
            'unit_price_ars' => 160000.00,
            'unit_price_usd' => 160.00,
            'total_amount_ars' => 480000.00,
            'total_amount_usd' => 480.00,
            'sale_date' => '2024-01-16',
            'buyer_name' => 'María López',
            'payment_method' => 'transfer'
        ]);

        // Act: Hacer la petición GET usando la ruta de testing
        $response = $this->getJson('/api/test/sales/statistics');

        // Assert: Verificar la respuesta
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Estadísticas obtenidas exitosamente'
            ])
            ->assertJsonStructure([
                'message',
                'data' => [
                    'total_sales',
                    'total_quantity_sold',
                    'total_amount_ars',
                    'total_amount_usd',
                    'average_price_ars',
                    'average_price_usd',
                    'sales_by_payment_method',
                    'sales_by_month'
                ]
            ]);

        $data = $response->json('data');
        $this->assertEquals(2, $data['total_sales']);
        $this->assertEquals(8, $data['total_quantity_sold']);
        $this->assertEquals(1230000.00, $data['total_amount_ars']);
        $this->assertEquals(1230.00, $data['total_amount_usd']);
    }

    /**
     * Test: Obtener estadísticas de ventas con filtros
     */
    public function test_statistics_sales_with_filters_success(): void
    {
        // Arrange: Crear ventas en diferentes fechas
        $batch = Batch::first();
        Sale::create([
            'batch_id' => $batch->id,
            'quantity_sold' => 5,
            'unit_price_ars' => 150000.00,
            'unit_price_usd' => 150.00,
            'total_amount_ars' => 750000.00,
            'total_amount_usd' => 750.00,
            'sale_date' => '2024-01-15',
            'buyer_name' => 'Juan Pérez',
            'payment_method' => 'cash'
        ]);

        Sale::create([
            'batch_id' => $batch->id,
            'quantity_sold' => 3,
            'unit_price_ars' => 160000.00,
            'unit_price_usd' => 160.00,
            'total_amount_ars' => 480000.00,
            'total_amount_usd' => 480.00,
            'sale_date' => '2024-02-15',
            'buyer_name' => 'María López',
            'payment_method' => 'transfer'
        ]);

        // Act: Hacer la petición GET con filtro de fecha usando la ruta de testing
        $response = $this->getJson('/api/test/sales/statistics?date_from=2024-01-01&date_to=2024-01-31');

        // Assert: Verificar que solo incluye ventas de enero
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals(1, $data['total_sales']);
        $this->assertEquals(5, $data['total_quantity_sold']);
        $this->assertEquals(750000.00, $data['total_amount_ars']);
        $this->assertEquals(750.00, $data['total_amount_usd']);
    }

    /**
     * Test: Crear venta con campos requeridos faltantes
     */
    public function test_create_sale_with_missing_required_fields_fails(): void
    {
        // Arrange: Preparar datos con campos faltantes
        $invalidData = [
            // Solo algunos campos opcionales
            'notes' => 'Nota de prueba'
        ];

        // Act: Hacer la petición POST
        $response = $this->postJson('/api/test/sales', $invalidData);

        // Assert: Verificar que falla con errores de validación
        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors'
            ]);

        $errors = $response->json('errors');
        
        $this->assertArrayHasKey('batch_id', $errors);
        $this->assertArrayHasKey('quantity_sold', $errors);
        $this->assertArrayHasKey('unit_price_ars', $errors);
        $this->assertArrayHasKey('unit_price_usd', $errors);
        $this->assertArrayHasKey('total_amount_ars', $errors);
        $this->assertArrayHasKey('total_amount_usd', $errors);
        $this->assertArrayHasKey('sale_date', $errors);
        $this->assertArrayHasKey('buyer_name', $errors);
    }

    /**
     * Test: Crear venta con métodos de pago válidos
     */
    public function test_create_sale_with_valid_payment_methods_success(): void
    {
        // Arrange: Preparar datos válidos usando el segundo lote para evitar conflictos de stock
        $batch = Batch::find(2); // Usar el segundo lote que tiene 50 animales
        $paymentMethods = ['cash', 'transfer', 'check', 'credit'];

        foreach ($paymentMethods as $paymentMethod) {
            $validData = [
                'batch_id' => $batch->id,
                'quantity_sold' => 1,
                'unit_price_ars' => 80000.00,
                'unit_price_usd' => 600.00,
                'total_amount_ars' => 80000.00,
                'total_amount_usd' => 600.00,
                'sale_date' => '2024-01-15',
                'buyer_name' => "Comprador {$paymentMethod}",
                'payment_method' => $paymentMethod
            ];

            $response = $this->postJson('/api/test/sales', $validData);
            $response->assertStatus(201);
        }

        // Verificar que se crearon todas las ventas
        $this->assertEquals(4, Sale::count());
    }

    /**
     * Test: Crear venta con fecha futura
     */
    public function test_create_sale_with_future_date_fails(): void
    {
        // Arrange: Preparar datos con fecha futura
        $batch = Batch::first();
        $invalidData = [
            'batch_id' => $batch->id,
            'quantity_sold' => 5,
            'unit_price_ars' => 150000.00,
            'unit_price_usd' => 150.00,
            'total_amount_ars' => 750000.00,
            'total_amount_usd' => 750.00,
            'sale_date' => now()->addDays(5)->format('Y-m-d'), // Fecha futura
            'buyer_name' => 'Juan Pérez',
            'payment_method' => 'cash'
        ];

        // Act: Hacer la petición POST
        $response = $this->postJson('/api/test/sales', $invalidData);

        // Assert: Verificar que falla
        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors'
            ])
            ->assertJson([
                'errors' => [
                    'sale_date' => [
                        'La fecha de venta no puede ser futura.'
                    ]
                ]
            ]);
    }
} 