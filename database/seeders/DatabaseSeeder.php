<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Brand;
use App\Models\Order;
use App\Models\Review;
use App\Models\Product;
use App\Models\Setting;
use App\Models\TaxRate;
use App\Models\Category;
use App\Models\Discount;
use App\Models\Attribute;
use App\Models\OrderItem;
use Illuminate\Support\Arr;
// use App\Models\ProductImage; // This line is removed
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use App\Models\AttributeValue;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Prepare Environment
        $this->command->info('Preparing database and storage...');
        // Clear media library storage directory if it exists
        Storage::disk('public')->deleteDirectory('media');
        // The media-library will create this directory as needed.

        DB::statement('PRAGMA foreign_keys = OFF');
        // Add all your table names here for a full reset
        $tables = [
            'users', 'products', 'categories', 'brands', 'orders', 'order_items',
            'reviews', 'discounts', 'attributes', 'attribute_values',
            'product_variants', 'settings', 'tax_rates', 'shipping_zones',
            'shipping_rates', 'category_product', 'attribute_product',
            'attribute_value_product_variant', 'media' 
        ];
        foreach ($tables as $table) {
            // Check if table exists before trying to delete
            if (DB::getSchemaBuilder()->hasTable($table)) {
                DB::table($table)->delete();
            }
        }
        DB::statement('PRAGMA foreign_keys = ON');

        // 2. Seed Foundational Data
        $this->command->info('Seeding foundational data...');
        User::factory()->create(['name' => 'Admin User', 'email' => 'admin@example.com', 'is_admin' => true]);
        User::factory(20)->create(); // Customers
        $brands = Brand::factory(10)->create();
        $categories = Category::factory(15)->create();

        $colorAttribute = Attribute::factory()->create(['name' => 'Color']);
        $colorValues = collect(['Red', 'Blue', 'Green', 'Black', 'White'])->map(fn($val) =>
            AttributeValue::factory()->create(['attribute_id' => $colorAttribute->id, 'value' => $val])
        );

        $sizeAttribute = Attribute::factory()->create(['name' => 'Size']);
        $sizeValues = collect(['S', 'M', 'L', 'XL'])->map(fn($val) =>
            AttributeValue::factory()->create(['attribute_id' => $sizeAttribute->id, 'value' => $val])
        );

        // 3. Seed Products
        $this->command->info('Seeding products...');

        // Create simple products. The factory now handles attaching images automatically.
        Product::factory(20)
            ->simple()
            ->create()
            ->each(function ($product) use ($categories) {
                $product->categories()->attach($categories->random(1, 2)->pluck('id'));
            });

        // Create products with variants. The factory also handles their images.
        for ($i = 0; $i < 30; $i++) {
            $product = Product::factory()->create(); // This automatically adds images now
            $product->categories()->attach($categories->random(rand(1, 2))->pluck('id'));
            $product->attributes()->sync([$colorAttribute->id, $sizeAttribute->id]);
            $combinations = Arr::crossJoin(
                $colorValues->random(rand(2, 4))->pluck('id')->all(),
                $sizeValues->random(rand(2, 3))->pluck('id')->all()
            );

            foreach ($combinations as $combination) {
                $colorId = $combination[0];
                $sizeId = $combination[1];

                $colorModel = $colorValues->firstWhere('id', $colorId);
                $sizeModel = $sizeValues->firstWhere('id', $sizeId);

                if ($colorModel && $sizeModel) {
                    $variantName = $colorModel->value . ' / ' . $sizeModel->value;
                    $variant = ProductVariant::factory()->create([
                        'product_id' => $product->id,
                        'name' => $variantName,
                        'price' => $product->price + rand(-5, 15)
                    ]);
                    $variant->attributeValues()->sync([$colorId, $sizeId]);
                }
            }
        }

        // 4. Seed Other Data (Reviews, Discounts, etc.)
        $this->command->info('Seeding other e-commerce data...');
        Product::all()->each(fn($p) => Review::factory(rand(0, 5))->create(['product_id' => $p->id]));
        Discount::factory(10)->create();
        Setting::create(['key' => 'site.name', 'value' => "Mella's Connect", 'group' => 'general', 'label' => 'Site Name']);
        ShippingZone::factory(3)->create()->each(fn($z) => $z->shippingRates()->createMany(ShippingRate::factory(2)->make()->toArray()));
        TaxRate::factory()->create(['name' => 'Standard VAT', 'rate' => 0.15]);

        // 5. Seed Orders
        $this->command->info('Seeding orders...');
        Order::factory()
            ->count(50)
            ->has(OrderItem::factory()->count(rand(1, 5)), 'items')
            ->create();

        $this->command->info('Database seeding completed successfully!');
    }

    // The seedProductImages() method has been completely removed
    // as this logic now lives inside the ProductFactory.
}