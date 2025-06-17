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
            'name' => 'Client Internal #1',
            'abbreviation' => 'I#1'
        ],
        [
            'name' => 'Client External #1',
            'abbreviation' => 'C#1'
        ],
        [
            'name' => 'Client External #2',
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
