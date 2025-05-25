<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->nullable()->constrained()->nullOnDelete(); // Link to user, set null if user deleted
            $table->string('order_number')->unique(); // Unique identifier for the order
            $table->string('status')->default('pending'); // e.g., pending, processing, shipped, delivered, cancelled, refunded
            $table->string('payment_status')->default('pending'); // e.g., pending, paid, failed, refunded
            $table->string('payment_method')->nullable();

            $table->decimal('subtotal', 10, 2); // Price of items before shipping/tax
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2); // Final amount paid

            // Store addresses as JSON for simplicity, or create separate address tables/models
            $table->json('shipping_address')->nullable();
            $table->json('billing_address')->nullable();

            $table->string('shipping_method')->nullable();
            $table->string('tracking_number')->nullable(); // For shipped orders
            $table->text('notes')->nullable(); // Internal admin notes

            $table->timestamp('paid_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps(); // created_at, updated_at

            $table->string('transaction_id')->nullable()->unique();
            $table->index('status');
            $table->index('payment_status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};