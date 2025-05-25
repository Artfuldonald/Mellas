<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Admin who made adjustment
            $table->morphs('adjustable'); // For Product or ProductVariant (polymorphic)
            $table->integer('quantity_change'); // Positive for increase, negative for decrease
            $table->integer('quantity_before');
            $table->integer('quantity_after');
            $table->string('reason')->nullable(); // e.g., "Stocktake", "Damaged Goods", "Return"
            $table->text('notes')->nullable(); // Additional admin notes
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};