<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This will remove the 'transaction_id' column from the 'orders' table.
     */
    public function up(): void
    {
        // First, check if the column exists to prevent errors on repeated runs.
        if (Schema::hasColumn('orders', 'transaction_id')) {
            Schema::table('orders', function (Blueprint $table) {
                // To drop a unique column, you might need to drop the index first.
                // The index name is typically 'table_column_unique'.
                $table->dropUnique('orders_transaction_id_unique');
                
                // Now, drop the column itself.
                $table->dropColumn('transaction_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     * This will add the 'transaction_id' column back if you roll back.
     */
    public function down(): void
    {
        // Check if the column does NOT exist before adding it back.
        if (!Schema::hasColumn('orders', 'transaction_id')) {
            Schema::table('orders', function (Blueprint $table) {
                // Add the column back with the same properties it had before.
                $table->string('transaction_id')->nullable()->unique()->after('order_number');
            });
        }
    }
};