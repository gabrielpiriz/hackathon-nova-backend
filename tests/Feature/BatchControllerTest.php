<?php

namespace Tests\Feature;

use App\Models\AnimalType;
use App\Models\Batch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BatchControllerTest extends TestCase
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
    }

    /**
     * Test de éxito: Crear lote con datos válidos
     */
    public function test_create_batch_with_valid_data_success(): void
    {
        // Arrange: Preparar datos válidos
        $animalType = AnimalType::first();
        $validData = [
            'animal_type_id' => $animalType->id,
            'quantity' => 50,
            'age_months' => 18,
            'average_weight_kg' => 450.75,
            'suggested_price_ars' => 150000.00,
            'suggested_price_usd' => 1200.50,
            'notes' => 'Lote de vacunos en excelente estado'
        ];

        // Act: Hacer la petición POST
        $response = $this->postJson('/api/test/batches', $validData);

        // Assert: Verificar la respuesta
        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Lote creado exitosamente'
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'producer' => ['id', 'full_name', 'email'],
                    'animal_type' => ['id', 'name', 'description'],
                    'quantity',
                    'age_months',
                    'average_weight_kg',
                    'suggested_price_ars',
                    'suggested_price_usd',
                    'status',
                    'status_label',
                    'notes',
                    'created_at',
                    'updated_at',
                    'created_at_formatted',
                    'sales_count',
                    'total_sold',
                    'remaining_quantity'
                ]
            ]);

        // Verificar que se creó en la base de datos
        $this->assertDatabaseHas('batches', [
            'animal_type_id' => $validData['animal_type_id'],
            'quantity' => $validData['quantity'],
            'age_months' => $validData['age_months'],
            'average_weight_kg' => $validData['average_weight_kg'],
            'suggested_price_ars' => $validData['suggested_price_ars'],
            'suggested_price_usd' => $validData['suggested_price_usd'],
            'notes' => $validData['notes'],
            'status' => 'available',
            'producer_id' => 1 // Usuario de prueba
        ]);

        // Verificar que se creó exactamente 1 lote
        $this->assertEquals(1, Batch::count());

        // Verificar los datos específicos del response
        $responseData = $response->json('data');
        $this->assertEquals($validData['quantity'], $responseData['quantity']);
        $this->assertEquals($validData['age_months'], $responseData['age_months']);
        $this->assertEquals(number_format($validData['average_weight_kg'], 2, '.', ''), $responseData['average_weight_kg']);
        $this->assertEquals('Disponible', $responseData['status_label']);
        $this->assertEquals(0, $responseData['sales_count']);
        $this->assertEquals($validData['quantity'], $responseData['remaining_quantity']);
    }

    /**
     * Test de error: Crear lote con datos inválidos
     */
    public function test_create_batch_with_invalid_data_fails(): void
    {
        // Arrange: Preparar datos inválidos
        $invalidData = [
            'animal_type_id' => 999, // ID que no existe
            'quantity' => -5, // Cantidad negativa
            'age_months' => 150, // Edad excesiva (>120 meses)
            'average_weight_kg' => 'invalid_weight', // Peso inválido
            'suggested_price_ars' => -100, // Precio negativo
            'suggested_price_usd' => 0, // Precio cero
            'notes' => str_repeat('x', 1001) // Notas demasiado largas (>1000 chars)
        ];

        // Act: Hacer la petición POST con datos inválidos
        $response = $this->postJson('/api/test/batches', $invalidData);

        // Assert: Verificar que falla con error 422
        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Error de validación'
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'errors'
            ]);

        // Verificar errores específicos de validación
        $errors = $response->json('errors');
        
        $this->assertArrayHasKey('animal_type_id', $errors);
        $this->assertArrayHasKey('quantity', $errors);
        $this->assertArrayHasKey('age_months', $errors);
        $this->assertArrayHasKey('average_weight_kg', $errors);
        $this->assertArrayHasKey('suggested_price_ars', $errors);
        $this->assertArrayHasKey('suggested_price_usd', $errors);
        $this->assertArrayHasKey('notes', $errors);

        // Verificar mensajes específicos en español
        $this->assertStringContainsString('no existe', $errors['animal_type_id'][0]);
        $this->assertStringContainsString('al menos 1', $errors['quantity'][0]);
        $this->assertStringContainsString('120 meses', $errors['age_months'][0]);
        $this->assertStringContainsString('mayor a 0', $errors['suggested_price_ars'][0]);
        $this->assertStringContainsString('mayor a 0', $errors['suggested_price_usd'][0]);
        $this->assertStringContainsString('1,000 caracteres', $errors['notes'][0]);

        // Verificar que NO se creó ningún lote en la base de datos
        $this->assertEquals(0, Batch::count());
        $this->assertDatabaseMissing('batches', [
            'animal_type_id' => $invalidData['animal_type_id']
        ]);
    }

    /**
     * Test adicional: Verificar campos requeridos
     */
    public function test_create_batch_with_missing_required_fields_fails(): void
    {
        // Arrange: Datos vacíos (todos los campos requeridos faltantes)
        $emptyData = [];

        // Act: Hacer la petición POST sin datos
        $response = $this->postJson('/api/test/batches', $emptyData);

        // Assert: Verificar error de validación
        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Error de validación'
            ]);

        $errors = $response->json('errors');

        // Verificar que todos los campos requeridos generan errores
        $requiredFields = [
            'animal_type_id',
            'quantity', 
            'age_months',
            'average_weight_kg',
            'suggested_price_ars',
            'suggested_price_usd'
        ];

        foreach ($requiredFields as $field) {
            $this->assertArrayHasKey($field, $errors);
            $this->assertStringContainsString('obligatori', $errors[$field][0]); // Acepta tanto "obligatorio" como "obligatoria"
        }

        // Verificar que NO se creó el lote
        $this->assertEquals(0, Batch::count());
    }
}
