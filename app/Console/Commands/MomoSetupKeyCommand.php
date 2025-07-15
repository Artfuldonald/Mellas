<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class MomoSetupKeyCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'momo:setup-key';

    /**
     * The console command description.
     */
    protected $description = '[Step 2] Requests a new API Key for the configured MTN MoMo API User.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('--- [Step 2] MTN MoMo API Key Creation ---');

        // --- 1. Gather Required Data ---
        $primaryKey = config('services.mtn_momo.collection.primary_key');
        $baseUrl = rtrim(config('services.mtn_momo.base_url'), '/');
        $userId = config('services.mtn_momo.collection.user_id');

        // --- 2. Validate Data ---
        if (empty($primaryKey) || empty($baseUrl) || empty($userId)) {
            $this->error('Required config (PRIMARY_KEY, BASE_URL, or USER_ID) is not set.');
            $this->comment('Please run `php artisan momo:setup-user` successfully first.');
            return self::FAILURE;
        }

        $this->line("Requesting API Key for User ID: <fg=yellow>{$userId}</>");

        // --- 3. Execute the API Call ---
        try {
            $url = "{$baseUrl}/v1_0/apiuser/{$userId}/apikey";

            $response = Http::withHeaders([
                'Ocp-Apim-Subscription-Key' => $primaryKey,
            ])->post($url);

            // --- 4. Handle the Response ---
            if ($response->status() === 201) {
                $apiKey = $response->json('apiKey');
                $this->info('SUCCESS: API Key successfully created!');
                $this->line("Your new API Key is: <fg=yellow>{$apiKey}</>");
                $this->updateEnvFile('MTN_MOMO_COLLECTION_API_KEY', $apiKey);
                $this->comment('User Provisioning Complete! You are now ready to request an access token.');
                return self::SUCCESS;
            }

            // Handle failure
            $this->error("FAILED: The API call failed with status code: {$response->status()}");
            $this->line("Response Body: " . ($response->body() ?: '(Empty)'));
            return self::FAILURE;

        } catch (\Exception $e) {
            $this->error('An exception occurred during the API call:');
            $this->line($e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Writes or updates a key in the .env file.
     */
    private function updateEnvFile(string $key, string $value): void
    {
        $envFilePath = app()->environmentFilePath();
        $content = file_get_contents($envFilePath);
        $escapedValue = str_contains($value, ' ') ? "\"{$value}\"" : $value;

        if (env($key) !== null && strpos($content, "{$key}=") !== false) {
            $content = preg_replace("/^{$key}=.*/m", "{$key}={$escapedValue}", $content);
            $this->line("Updated <fg=cyan>{$key}</> in your .env file.");
        } else {
            $content .= "\n{$key}={$escapedValue}\n";
            $this->line("Added <fg=cyan>{$key}</> to your .env file.");
        }

        file_put_contents($envFilePath, $content);
    }
}