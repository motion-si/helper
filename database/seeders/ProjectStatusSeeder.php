<?php

namespace Database\Seeders;

use App\Models\ProjectStatus;
use Illuminate\Database\Seeder;

class ProjectStatusSeeder extends Seeder
{
    /**
     * Set the execution order for this seeder.
     */
    public function executionOrder()
    {
        return 70;
    }

    private array $data = [
        [
            'name' => 'Backlog',
            'color' => '#cecece',
            'is_default' => false
        ],
        [
            'name' => 'Development',
            'color' => '#000aff',
            'is_default' => false
        ],
        [
            'name' => 'Active',
            'color' => '#008000',
            'is_default' => true
        ],
        [
            'name' => 'Deprecated',
            'color' => '#ff0000',
            'is_default' => false
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
            ProjectStatus::firstOrCreate($item);
        }
    }
}
