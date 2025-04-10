<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
     public function up(): void
     {
         Schema::create('attribute_product', function (Blueprint $table) {
             $table->foreignId('attribute_id')->constrained('attributes')->onDelete('cascade');
             $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
             $table->primary(['attribute_id', 'product_id']); // Composite primary key
         });
     }
    public function down(): void 
    {
         Schema::dropIfExists('attribute_product');
    }
};