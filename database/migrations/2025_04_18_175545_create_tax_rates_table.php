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
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., "Standard VAT", "GST", "Sales Tax - CA"
            $table->decimal('rate', 8, 4); // Store rate as a decimal (e.g., 0.07 for 7%, 0.20 for 20%) - Use sufficient precision
            $table->integer('priority')->default(1); // For handling compound taxes later if needed
            $table->boolean('is_active')->default(true);
            // $table->boolean('apply_to_shipping')->default(false); // Optional: Tax shipping cost?
            // $table->foreignId('tax_zone_id')->nullable()->constrained(); // Optional: Link to zones later
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_rates');
    }
};