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
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('price_at_add', 10, 2); 
            $table->json('variant_data')->nullable(); 
            $table->timestamps();
            
            // Unique constraints (prevent duplicate cart items)
            $table->unique(['session_id', 'product_id', 'variant_id'], 'cart_session_product_variant_unique');
            $table->unique(['user_id', 'product_id', 'variant_id'], 'cart_user_product_variant_unique');
            
            // Performance indexes for fast lookups
            $table->index('session_id', 'carts_session_id_index');
            $table->index('user_id', 'carts_user_id_index');
            $table->index(['user_id', 'product_id'], 'carts_user_product_index');
            $table->index(['session_id', 'product_id'], 'carts_session_product_index');
            $table->index('created_at', 'carts_created_at_index'); // For cleanup tasks
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
