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
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_zone_id')
                  ->constrained('shipping_zones') // Links to the 'shipping_zones' table
                  ->onDelete('cascade'); // If a zone is deleted, delete its rates too

            $table->string('name'); // e.g., "Standard", "Express", "Free Shipping"
            $table->decimal('cost', 10, 2)->default(0.00); // The cost of this shipping method
            $table->boolean('is_active')->default(true); // Enable/disable this specific rate
            $table->text('description')->nullable(); // Optional description shown to customer

            // --- Optional Future Criteria ---
            // Add columns here later if you need rates based on conditions
            // Example: Free shipping over a certain amount
            // $table->decimal('min_order_subtotal', 10, 2)->nullable();
            // Example: Rate based on weight range
            // $table->decimal('min_weight', 8, 2)->nullable();
            // $table->decimal('max_weight', 8, 2)->nullable();
            // -----------------------------
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_rates');
    }
};
