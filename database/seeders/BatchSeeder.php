<?php

namespace Database\Seeders;

use App\Models\AnimalType;
use App\Models\Batch;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BatchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();
        
        // Obtener tipos de animales
        $bovino = AnimalType::where('name', 'Vacuno')->first();
        $ovino = AnimalType::where('name', 'Ovino')->first();
        $equino = AnimalType::where('name', 'Equino')->first();
        $porcino = AnimalType::where('name', 'Porcino')->first();
        
        // Datos de lotes similares a la imagen
        $batchesData = [
            [
                'animal_type_id' => $bovino->id,
                'quantity' => 10,
                'age_months' => 48, // 4 años
                'average_weight_kg' => 450.00,
                'suggested_price_ars' => 180000.00,
                'suggested_price_usd' => 500.00,
                'status' => 'sold', // Vendido
                'notes' => 'Lote de bovinos premium, excelente genética'
            ],
            [
                'animal_type_id' => $ovino->id,
                'quantity' => 25,
                'age_months' => 60, // 5 años
                'average_weight_kg' => 65.00,
                'suggested_price_ars' => 50000.00,
                'suggested_price_usd' => 2500.00,
                'status' => 'sold', // Vendido
                'notes' => 'Ovejas de lana de alta calidad'
            ],
            [
                'animal_type_id' => $equino->id,
                'quantity' => 3,
                'age_months' => 36, // 3 años
                'average_weight_kg' => 450.00,
                'suggested_price_ars' => 300000.00,
                'suggested_price_usd' => 1500.00,
                'status' => 'available', // No vendido
                'notes' => 'Caballos de trabajo, muy dóciles'
            ],
            [
                'animal_type_id' => $porcino->id,
                'quantity' => 20,
                'age_months' => 72, // 6 años
                'average_weight_kg' => 180.00,
                'suggested_price_ars' => 75000.00,
                'suggested_price_usd' => 750.00,
                'status' => 'sold', // Vendido
                'notes' => 'Cerdos reproductores de raza'
            ],
            [
                'animal_type_id' => $bovino->id,
                'quantity' => 15,
                'age_months' => 24, // 2 años
                'average_weight_kg' => 350.00,
                'suggested_price_ars' => 120000.00,
                'suggested_price_usd' => 600.00,
                'status' => 'available', // No vendido
                'notes' => 'Terneros jóvenes, ideales para engorde'
            ],
            [
                'animal_type_id' => $ovino->id,
                'quantity' => 40,
                'age_months' => 18, // 1.5 años
                'average_weight_kg' => 45.00,
                'suggested_price_ars' => 35000.00,
                'suggested_price_usd' => 175.00,
                'status' => 'available', // No vendido
                'notes' => 'Corderos para carne, alimentados a pasto'
            ]
        ];
        
        // Crear los lotes
        foreach ($batchesData as $batchData) {
            $batch = Batch::create([
                'producer_id' => $user->id,
                'animal_type_id' => $batchData['animal_type_id'],
                'quantity' => $batchData['quantity'],
                'age_months' => $batchData['age_months'],
                'average_weight_kg' => $batchData['average_weight_kg'],
                'suggested_price_ars' => $batchData['suggested_price_ars'],
                'suggested_price_usd' => $batchData['suggested_price_usd'],
                'status' => $batchData['status'],
                'notes' => $batchData['notes']
            ]);
            
            // Si el lote está vendido, crear una venta
            if ($batchData['status'] === 'sold') {
                Sale::create([
                    'batch_id' => $batch->id,
                    'quantity_sold' => $batch->quantity, // Vender todo el lote
                    'unit_price_ars' => $batch->suggested_price_ars / $batch->quantity,
                    'unit_price_usd' => $batch->suggested_price_usd / $batch->quantity,
                    'total_amount_ars' => $batch->suggested_price_ars,
                    'total_amount_usd' => $batch->suggested_price_usd,
                    'sale_date' => now()->subDays(rand(1, 30)), // Venta en los últimos 30 días
                    'buyer_name' => $this->getRandomBuyerName(),
                    'buyer_contact' => 'comprador@email.com',
                    'payment_method' => 'transfer',
                    'notes' => 'Venta completa del lote'
                ]);
            }
        }
    }
    
    /**
     * Obtener nombres aleatorios de compradores
     */
    private function getRandomBuyerName(): string
    {
        $names = [
            'Frigorífico San Miguel',
            'Estancia El Progreso',
            'Carnicería Los Álamos',
            'Exportadora Ganadera SA',
            'Cooperativa de Productores',
            'Matadero Municipal',
            'Granja Familiar López',
            'Distribuidora de Carnes Norte'
        ];
        
        return $names[array_rand($names)];
    }
}
