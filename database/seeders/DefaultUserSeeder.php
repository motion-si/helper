<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserClient;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DefaultUserSeeder extends Seeder
{
    /**
     * Set the execution order for this seeder.
     */
    public function executionOrder()
    {
        return 10;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (User::where('email', 'test_project_manager@test.com')->count() == 0) {
            $user = User::create([
                'name' => 'Test Project Manager',
                'email' => 'test_project_manager@test.com',
                'password' => bcrypt('Passw@rd'),
                'email_verified_at' => now()
            ]);
            $user->creation_token = null;
            $user->save();

            // Assign to the internal company client
            UserClient::create([
                'user_id' => $user->id,
                'client_id' => 1, // Internal Company Client ID
            ]);

            $user = User::create([
                'name' => 'Test Developer',
                'email' => 'test_developer@test.com',
                'password' => bcrypt('Passw@rd'),
                'email_verified_at' => now()
            ]);
            $user->creation_token = null;
            $user->save();

            // Assign to the internal company client
            UserClient::create([
                'user_id' => $user->id,
                'client_id' => 1, // Internal Company Client ID
            ]);

            $user = User::create([
                'name' => 'Test Customer',
                'email' => 'test_customer@test.com',
                'password' => bcrypt('Passw@rd'),
                'email_verified_at' => now()
            ]);
            $user->creation_token = null;
            $user->save();

            // Assign to the external company client
            UserClient::create([
                'user_id' => $user->id,
                'client_id' => 2, // External Company Client ID
            ]);
        }
    }
}
