<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Order;
use App\Models\Product; 
use App\Models\OrderItem;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Generator as FakerGenerator;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'is_admin' => true, 
            'email_verified_at' => now()
        ]);
        $this->command->info('Default admin user created.');       
          
        
    if (Product::count() === 0) {        
        try {
            Product::factory()->hasVariants(3)->create();
            $this->command->info('Created a factory product with variants.');
        } catch (\Exception $e) {
            $this->command->error('Failed to create factory product: ' . $e->getMessage());
            $this->command->warn('Please ensure you have Product/Variant factories set up correctly or seed them manually.');
            return; // Stop seeding if product creation fails
        }
   }
        // Ensure at least one variant exists for the OrderItemFactory to work reliably
        if (ProductVariant::count() === 0 && Product::count() > 0) {
             Product::first()->variants()->create([
                'name' => 'Default Variant',
                'sku' => 'DEFAULT-VAR-SKU',
                'price' => Product::first()->price,
                'quantity' => 100,
             ]);
             $this->command->info('Created a default variant as none existed.');
        }


        if (ProductVariant::count() > 0 || Product::whereDoesntHave('variants')->count() > 0) {
            $this->command->info('Seeding Orders and Order Items...');
            // Resolve the Faker instance from Laravel's service container
            $faker = $this->container->make(FakerGenerator::class); // <-- Resolve Faker
        
            Order::factory()
                ->count(50) // Create 50 orders
                // Use the resolved $faker variable
                ->has(OrderItem::factory()->count($faker->numberBetween(1, 5)), 'items')
                ->create();
        
            $this->command->info('Orders and Order Items seeded.');
        } else {
             $this->command->warn('Skipping Order seeding because no Products or Variants were found.');
        }
        
        $this->call(CategorySeeder::class);
        $this->call(CustomerSeeder::class);
    }
}