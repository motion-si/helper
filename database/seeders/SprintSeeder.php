<?php

namespace Database\Seeders;

use App\Models\Sprint;
use Illuminate\Database\Seeder;

class SprintSeeder extends Seeder
{
    /**
     * Set the execution order for this seeder.
     */
    public function executionOrder()
    {
        return 80;
    }

    private array $data = [
        [
            'name' => 'Sprint Example #1 Client Internal #1',
            'starts_at' => '2025-06-01',
            'ends_at' => '2025-06-30',
            'description' => '<p>Test example #1</p>',
            'client_id' => 1,
        ],
        [
            'name' => 'Sprint Example #2 Client External #1',
            'starts_at' => '2025-06-01',
            'ends_at' => '2025-06-30',
            'description' => '<p>Test example #2</p>',
            'client_id' => 2,
        ],
        [
            'name' => 'Sprint Example #3 Client External #2',
            'starts_at' => '2025-06-01',
            'ends_at' => '2025-06-30',
            'description' => '<p>Test example #3</p>',
            'client_id' => 3,
        ],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->data as $item) {
            Sprint::firstOrCreate($item);
        }
    }
}
