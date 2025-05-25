<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Order::class)->constrained()->cascadeOnDelete(); // Link to orders table

            // Link to either Product or ProductVariant
            $table->foreignIdFor(Product::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(ProductVariant::class)->nullable()->constrained()->nullOnDelete();

            // Store product details at the time of order (snapshot)
            $table->string('product_name');
            $table->string('variant_name')->nullable(); // e.g., "Red / Large"
            $table->string('sku')->nullable();
            $table->decimal('price', 10, 2); // Price per item at time of order
            $table->unsignedInteger('quantity');
            $table->decimal('line_total', 10, 2); // price * quantity

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};