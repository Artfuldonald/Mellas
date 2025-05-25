<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User; 

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a specific number of customer users (e.g., 50)
        User::factory()
            ->count(50) // Adjust the number as needed
            ->create([
                'is_admin' => false, // Ensure these are customers
            ]);

        // You could also create one specific test customer with known credentials
        // User::factory()->create([
        //     'name' => 'Test Customer',
        //     'email' => 'customer@example.com',
        //     'password' => bcrypt('password'), // Or Hash::make('password')
        //     'is_admin' => false,
        //     'email_verified_at' => now(),
        // ]);

         $this->command->info('Customer seeder finished.');
    }
}