<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wishlist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            //allow specific variants in wishlist
            $table->foreignId('product_variant_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();

            // Ensure a user can only add a specific product (and variant) once
            $table->unique(['user_id', 'product_id']);
            
             $table->unique(['user_id', 'product_id', 'product_variant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wishlist_items');
    }
};