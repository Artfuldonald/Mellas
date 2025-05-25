<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // The unique identifier for the setting (e.g., 'store_name')
            $table->text('value')->nullable(); // The value of the setting (use text for flexibility)
            $table->string('type')->default('string'); // Optional: Hint for how to treat/display the value (string, text, boolean, number)
            $table->string('group')->default('general'); // Optional: Group settings in the UI (e.g., 'general', 'mail', 'payments')
            $table->string('label'); // User-friendly label for the setting in the UI
            $table->text('description')->nullable(); // Optional description/hint for the admin
            $table->timestamps(); 

            $table->index('group');
            
        });

        $defaults = [
            [
                'key' => 'store_name',
                'value' => config('app.name', 'Mella\'s Connect'), // Default to app name
                'type' => 'string',
                'group' => 'general',
                'label' => 'Store Name',
                'description' => 'The public name of your store.',
            ],
            [
                'key' => 'store_email',
                'value' => 'admin@example.com', // Replace with a real default later
                'type' => 'string',
                'group' => 'general',
                'label' => 'Store Contact Email',
                'description' => 'The main email address for store contact and notifications.',
            ],
             [
                'key' => 'store_phone',
                'value' => null,
                'type' => 'string',
                'group' => 'general',
                'label' => 'Store Phone Number',
                'description' => 'Optional public phone number for the store.',
            ],
             [
                'key' => 'store_address',
                'value' => null,
                'type' => 'text', // Use text for multi-line address
                'group' => 'general',
                'label' => 'Store Address',
                'description' => 'The physical or mailing address of the store (used for invoices, etc.).',
            ],
            [
                'key' => 'currency_symbol',
                'value' => '$', // Default symbol
                'type' => 'string',
                'group' => 'general',
                'label' => 'Currency Symbol',
                'description' => 'The symbol displayed for prices (e.g., $, £, €, GH₵).',
            ],
             [
                'key' => 'currency_code',
                'value' => 'USD', // Default code
                'type' => 'string',
                'group' => 'general',
                'label' => 'Currency Code (ISO)',
                'description' => 'The 3-letter ISO currency code (e.g., USD, GBP, GHS). Used for payment gateways.',
            ],
            // Add more groups/settings as needed (e.g., 'mail', 'social_links')
        ];

        foreach ($defaults as $setting) {
            // Use updateOrCreate to avoid errors if seeding runs multiple times
            Setting::updateOrCreate(
                ['key' => $setting['key']], // Find by key
                $setting // Data to insert or update with
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};