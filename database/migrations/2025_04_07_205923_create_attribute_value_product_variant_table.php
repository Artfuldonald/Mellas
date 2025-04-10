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
        Schema::create('attribute_value_product_variant', function (Blueprint $table) {
            // Foreign key to attribute_values table
            $table->foreignId('attribute_value_id')
                  ->constrained('attribute_values')
                  ->onDelete('cascade'); // Delete link if value is deleted

            // Foreign key to product_variants table
            $table->foreignId('product_variant_id')
                  ->constrained('product_variants')
                  ->onDelete('cascade'); // Delete link if variant is deleted

            // Primary key to prevent duplicates (a variant shouldn't have the same value twice)
            $table->primary(['attribute_value_id', 'product_variant_id'], 'attr_val_prod_var_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attribute_value_product_variant');
    }
};