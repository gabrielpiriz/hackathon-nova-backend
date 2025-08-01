<?php

namespace Database\Seeders;

use App\Models\AnimalType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AnimalTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $animalTypes = [
            [
                'name' => 'Vacuno',
                'description' => 'Ganado bovino para producción de carne'
            ],
            [
                'name' => 'Ovino',
                'description' => 'Ganado ovino para producción de carne y lana'
            ],
            [
                'name' => 'Porcino',
                'description' => 'Ganado porcino para producción de carne'
            ],
            [
                'name' => 'Caprino',
                'description' => 'Ganado caprino para producción de carne y leche'
            ],
            [
                'name' => 'Equino',
                'description' => 'Ganado equino para trabajo y deporte'
            ],
            [
                'name' => 'Aviar',
                'description' => 'Aves de corral para producción de carne y huevos'
            ]
        ];

        foreach ($animalTypes as $type) {
            AnimalType::firstOrCreate(
                ['name' => $type['name']],
                ['description' => $type['description']]
            );
        }
    }
}
