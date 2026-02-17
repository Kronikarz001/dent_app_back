<?php

namespace Database\Seeders;

use App\Models\JobPosition;
use Illuminate\Database\Seeder;

/**
 * Summary of JobPositionSeeder
 */
class JobPositionSeeder extends Seeder
{
    /**
     * @return void
     */
    public function run(): void
    {
        collect($this->positions)->each(function ($position) {
            JobPosition::create([
                'name' => $position['name'],
                'f_name' => $position['f_name'],
                'm_name' => $position['m_name'],
            ]);
        });
    }

    /**
     * @var array|array[]
     */
    private array $positions = [
        [
            'name' => 'Stanowisko lekarza stomatologa',
            'f_name' => 'Lekarka stomatolożka',
            'm_name' => 'Lekarz stomatolog',
        ],
        [
            'name' => 'Stanowisko higienisty stomatologicznego',
            'f_name' => 'Higienistka stomatologiczna',
            'm_name' => 'Higienista stomatologiczny',
        ],
        [
            'name' => 'Stanowisko kierownicze',
            'f_name' => 'Kierowniczka',
            'm_name' => 'Kierownik',
        ],
        [
            'name' => 'Stanowisko menedżerskie',
            'f_name' => 'Menedżerka',
            'm_name' => 'Menedżer',
        ],
        [
            'name' => 'Stanowisko opiekuna recepcji',
            'f_name' => 'Recepcjonistka',
            'm_name' => 'Recepcjonista',
        ],
    ];
}
