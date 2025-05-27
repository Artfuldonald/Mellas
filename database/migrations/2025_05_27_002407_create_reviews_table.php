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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Optional: if reviews can be anonymous or users can be deleted
            $table->string('reviewer_name')->nullable(); // For guest reviews
            $table->string('reviewer_email')->nullable(); // For guest reviews
            $table->unsignedTinyInteger('rating')->comment('Rating from 1 to 5');
            $table->string('title')->nullable();
            $table->text('comment');
            $table->boolean('is_approved')->default(false); // For moderation
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};