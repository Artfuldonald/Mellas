<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Order;

return new class extends Migration
{
   
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Order::class)->constrained()->cascadeOnDelete(); 
            
            $table->string('payment_method'); 
            $table->string('status'); 
            
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('GHS');
            
            // References for tracking payments
            $table->string('payment_reference')->nullable()->unique(); 
            $table->string('transaction_id')->nullable()->unique();
            
            // For storing additional data
            $table->text('failure_reason')->nullable();
            $table->json('gateway_response')->nullable();
            
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};