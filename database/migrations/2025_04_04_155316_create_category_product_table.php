<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('category_product', function (Blueprint $table) {
            // Foreign key for Category
            $table->foreignId('category_id')
                  ->constrained('categories') // Link to categories table
                  ->onDelete('cascade'); // If category deleted, remove link

            // Foreign key for Product
            $table->foreignId('product_id')
                  ->constrained('products') // Link to products table
                  ->onDelete('cascade'); // If product deleted, remove link

            // --- IMPORTANT: Composite Primary Key ---
            // This prevents adding the same product to the same category twice
            // and also acts as an efficient index for lookups.
            $table->primary(['category_id', 'product_id']);
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_product');
    }
};