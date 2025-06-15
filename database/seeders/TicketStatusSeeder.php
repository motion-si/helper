<?php

namespace Database\Seeders;

use App\Models\TicketStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TicketStatusSeeder extends Seeder
{
    /**
     * Set the execution order for this seeder.
     */
    public function executionOrder()
    {
        return 50;
    }

    private array $data = [
        [
            'name' => 'Backlog',
            'color' => '#cecece',
            'is_default' => true,
            'order' => 1
        ],
        [
            'name' => 'Sprint',
            'color' => '#ff7f00',
            'is_default' => false,
            'order' => 2
        ],
        [
            'name' => 'Test',
            'color' => '#faff00',
            'is_default' => false,
            'order' => 3
        ],
        [
            'name' => 'Approved',
            'color' => '#000aff',
            'is_default' => false,
            'order' => 4
        ],
        [
            'name' => 'Released',
            'color' => '#008000',
            'is_default' => false,
            'order' => 5
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
            TicketStatus::firstOrCreate($item);
        }
    }
}
