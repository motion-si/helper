<?php

namespace Database\Seeders;

use App\Models\Ticket;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    /**
     * Set the execution order for this seeder.
     */
    public function executionOrder()
    {
        return 85;
    }

    private array $data = [
        [
            'name' => 'Ticket Example #1',
            'content' => '<p>Test example #1</p>',
            'owner_id' => 3,
            'responsible_id' => 1,
            'status_id' => 1,
            'project_id' => 1,
            'code' => '001-1',
            'type_id' => 2,
            'order' => 1,
            'priority_id' => 2,
            'estimation' => '04:30:00',
            'credits' => 1,
        ],
        [
            'name' => 'Ticket Example #2',
            'content' => '<p>Test example #2</p>',
            'owner_id' => 3,
            'responsible_id' => 1,
            'status_id' => 1,
            'project_id' => 1,
            'code' => '002-1',
            'type_id' => 2,
            'order' => 1,
            'priority_id' => 2,
            'estimation' => '04:30:00',
            'credits' => 1,
            'sprint_id' => 1,
        ],
        [
            'name' => 'Ticket Example #3',
            'content' => '<p>Test example #3</p>',
            'owner_id' => 1,
            'responsible_id' => 1,
            'status_id' => 1,
            'project_id' => 1,
            'code' => '003-1',
            'type_id' => 2,
            'order' => 1,
            'priority_id' => 2,
            'estimation' => '04:30:00',
            'credits' => 1,
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
            Ticket::firstOrCreate($item);
        }
    }
}
