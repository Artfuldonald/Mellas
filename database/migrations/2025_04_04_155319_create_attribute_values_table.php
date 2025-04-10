<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained('attributes')->onDelete('cascade'); // Link to attributes, delete values if attribute is deleted
            $table->string('value');
            $table->string('slug');
            $table->timestamps();

            // Prevent duplicate values within the SAME attribute (e.g., two 'Red' for 'Color')
            $table->unique(['attribute_id', 'value']);
            $table->index('slug');
        });
    }
    public function down(): void 
    { 
        Schema::dropIfExists('attribute_values');
    }
};