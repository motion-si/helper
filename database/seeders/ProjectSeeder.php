<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Set the execution order for this seeder.
     */
    public function executionOrder()
    {
        return 75;
    }

    private array $data = [
        [
            'name' => 'Example #1',
            'description' => '<p>Test example #1</p>',
            'owner_id' => 3,
            'status_id' => 3,
            'ticket_prefix' => '001',
            'status_type' => 'default',
            'type' => 'scrum',
            'client_id' => 2,
        ],
        [
            'name' => 'Example #2',
            'description' => '<p>Test example #2</p>',
            'owner_id' => 3,
            'status_id' => 3,
            'ticket_prefix' => '002',
            'status_type' => 'default',
            'type' => 'scrum',
            'client_id' => 2,
        ],
        [
            'name' => 'Example #3',
            'description' => '<p>Test example #3</p>',
            'owner_id' => 1,
            'status_id' => 3,
            'ticket_prefix' => '003',
            'status_type' => 'default',
            'type' => 'scrum',
            'client_id' => 1,
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
            Project::firstOrCreate($item);
        }
    }
}
