<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Quadrant;
use App\Models\District;

class QuadrantAndDistrictSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Quadrant 1.1' => [
                'Labuan District',
                'Ayala District',
                'Baliwasan District',
            ],
            'Quadrant 1.2' => [
                'Sta. Maria District',
                'Zambo. Central District',
            ],
            'Quadrant 2.1' => [
                'Curuan District',
                'Manicahan District',
            ],
            'Quadrant 2.2' => [
                'Putik District',
                'Tetuan District',
                'Mercedes District',
                'Talon-talon District',
            ],
        ];

        foreach ($data as $quadrantName => $districts) {
            $quadrant = Quadrant::updateOrCreate(
                ['name' => $quadrantName]
            );

            foreach ($districts as $districtName) {
                District::updateOrCreate(
                    [
                        'name' => $districtName,
                    ],
                    [
                        'quadrant_id' => $quadrant->id,
                    ]
                );
            }
        }
    }
}