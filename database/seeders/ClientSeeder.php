<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Set the execution order for this seeder.
     */
    public function executionOrder()
    {
        return 01;
    }

    private array $data = [
        [
            'name' => 'Internal Company',
            'abbreviation' => 'INT'
        ],
        [
            'name' => 'Client Example #1',
            'abbreviation' => 'C#1'
        ],
        [
            'name' => 'Client Example #2',
            'abbreviation' => 'C#2'
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
            Client::firstOrCreate($item);
        }
    }
}
