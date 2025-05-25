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
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // The coupon code itself (e.g., "SUMMER10")
            $table->text('description')->nullable(); // Internal description for admin
            $table->enum('type', ['percentage', 'fixed_amount'])->default('fixed_amount'); // Type of discount
            $table->decimal('value', 10, 2); // Amount (if fixed) or Percentage (e.g., 10.00 for 10%)
            $table->decimal('min_spend', 10, 2)->nullable(); // Minimum order subtotal to apply
            $table->unsignedInteger('max_uses')->nullable(); // Max total uses for this code
            $table->unsignedInteger('max_uses_per_user')->nullable(); // Max uses per customer
            $table->timestamp('starts_at')->nullable(); // When the coupon becomes active
            $table->timestamp('expires_at')->nullable(); // When the coupon expires
            $table->boolean('is_active')->default(true); // Enable/disable the coupon
            $table->unsignedInteger('times_used')->default(0); // Counter for how many times it's been used
            $table->timestamps();
            // Indexes
            $table->index('is_active');
            $table->index('starts_at');
            $table->index('expires_at');
           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};