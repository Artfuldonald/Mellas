<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Support\Str;
// use App\Models\ProductImage; // This line is removed
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true);
        $price = $this->faker->randomFloat(2, 20, 500);

        return [
            // --- Basic Info ---
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'description' => '<h3>' . $this->faker->sentence(5) . '</h3><p>' . implode('</p><p>', $this->faker->paragraphs(4)) . '</p>',
            'short_description' => $this->faker->sentence(20),
            'brand_id' => Brand::inRandomOrder()->first()->id ?? Brand::factory(),

            // --- Specifications (JSON field) ---
            'specifications' => [
                ['key' => 'Material', 'value' => $this->faker->randomElement(['Cotton', 'Polyester', 'Leather', 'Aluminum'])],
                ['key' => 'Origin', 'value' => $this->faker->country()],
                ['key' => 'Warranty', 'value' => $this->faker->randomElement(['1 Year', '2 Years', 'No Warranty'])],
                ['key' => 'Model Number', 'value' => 'MDL-' . $this->faker->randomNumber(6)],
            ],

            // --- Pricing ---
            'price' => $price,
            'compare_at_price' => $this->faker->optional(0.4)->randomFloat(2, $price + 10, $price + 100), // 40% chance of having a discount
            'cost_price' => $this->faker->optional(0.7)->randomFloat(2, 10, $price - 5),

            // --- Status Flags ---
            'is_active' => true,
            'is_featured' => $this->faker->boolean(25), // 25% chance of being featured

            // --- SEO ---
            'meta_title' => Str::title($name) . ' | Mella\'s Connect',
            'meta_description' => Str::limit($this->faker->sentence(25), 155),

            // --- Shipping ---
            'weight' => $this->faker->randomFloat(2, 0.1, 5.0),
            'weight_unit' => 'kg',
            'dimensions' => $this->faker->numberBetween(10, 50) . 'x' . $this->faker->numberBetween(10, 50) . 'x' . $this->faker->numberBetween(2, 20) . ' cm',

            // Note: 'sku' and 'quantity' are handled by the simple() state factory
        ];
    }

    /**
     * Configure the model factory to attach images using MediaLibrary after creation.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Product $product) {
            // Find the seeder images
            $seedImagesPath = database_path('seeders/images');
            if (!is_dir($seedImagesPath)) {
                return; // Do nothing if the images folder doesn't exist
            }
            $allImages = collect(glob($seedImagesPath . '/*.{jpg,jpeg,png}', GLOB_BRACE));

            if ($allImages->isEmpty()) {
                return; // Do nothing if there are no images to attach
            }

            // Take 2-4 random images for each product, ensuring we don't request more than exist
            $imageCount = min($allImages->count(), rand(2, 4));
            
            foreach ($allImages->random($imageCount) as $imagePath) {
                // Attach the image using the new MediaLibrary way
                $product->addMedia($imagePath)
                        ->preservingOriginal()
                        ->toMediaCollection('default');
            }
        });
    }

    /**
     * Define a state for a simple product without variants.
     */
    public function simple(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'sku' => $this->faker->unique()->bothify('PROD-######'),
                'quantity' => $this->faker->numberBetween(10, 200),
            ];
        });
    }
}