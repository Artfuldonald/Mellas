<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Filesystem\Filesystem; // Import the Filesystem class

class ProductImageFactory extends Factory
{
    public function definition(): array
    {
        // 1. Define the source directory for your placeholder images.
        $sourceDir = database_path('seeders/images');
        
        // 2. Define the destination directory within `storage/app/public`.
        $destinationDir = 'product-images';

        // 3. Ensure the destination directory exists.
        Storage::disk('public')->makeDirectory($destinationDir);

        // 4. Use the Filesystem helper to get all files. This is more robust.
        $filesystem = new Filesystem();
        $files = $filesystem->files($sourceDir);

        // 5. Check if any source images were found.
        if (empty($files)) {
            // This is your safety net.
            return [
                'path' => 'path/to/default-placeholder.jpg',
                'alt' => 'Placeholder image',
                'position' => 0,
            ];
        }

        // 6. Pick a random file object from the array.
        $randomFile = $files[array_rand($files)];

        // 7. Generate a new unique filename, keeping the original extension.
        $newFileName = Str::uuid() . '.' . $randomFile->getExtension();
        
        // 8. Define the full database path (relative to `public/storage`).
        $dbPath = $destinationDir . '/' . $newFileName;

        // 9. Copy the random image from your seeder folder to the public storage folder.
        Storage::disk('public')->put(
            $dbPath,
            $randomFile->getContents() // Use the object's getContents() method
        );

        // 10. Return the correct path to be saved in the database.
        return [
            'path' => $dbPath,
            'alt' => $this->faker->sentence(3),
            'position' => 0,
        ];
    }
}