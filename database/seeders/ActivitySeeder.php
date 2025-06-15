<?php

namespace Database\Seeders;

use App\Models\Activity;
use Illuminate\Database\Seeder;

class ActivitySeeder extends Seeder
{
    /**
     * Set the execution order for this seeder.
     */
    public function executionOrder()
    {
        return 60;
    }

    private array $data = [
        [
            'name' => 'Programming',
            'description' => 'Programming related activities'
        ],
        [
            'name' => 'Testing',
            'description' => 'Testing related activities'
        ],
        [
            'name' => 'Scoping',
            'description' => 'Activities related to scoping and planning'
        ],
        [
            'name' => 'Meeting',
            'description' => 'Activities related to meetings'
        ],
        [
            'name' => 'Learning',
            'description' => 'Activities related to learning and training'
        ],
        [
            'name' => 'Research',
            'description' => 'Activities related to research'
        ],
        [
            'name' => 'Other',
            'description' => 'Other activities'
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
            Activity::firstOrCreate($item);
        }
    }
}
