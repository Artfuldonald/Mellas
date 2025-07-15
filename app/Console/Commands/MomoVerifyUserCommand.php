<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Ramsey\Uuid\Uuid;

class MomoVerifyUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     * We allow an optional argument to check any user ID.
     */
    protected $signature = 'momo:verify-user {userId? : The API User ID to verify. If not provided, uses the one in .env}';

    /**
     * The console command description.
     */
    protected $description = '[Step 1.5] Verifies that an API User exists in the MTN MoMo Sandbox.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('--- [Step 1.5] MTN MoMo API User Verification ---');

        // --- 1. Gather Required Data ---
        $primaryKey = config('services.mtn_momo.collection.primary_key');
        $baseUrl = rtrim(config('services.mtn_momo.base_url'), '/');
        
        // Use the ID from the command argument, or fall back to the .env file.
        $userIdToVerify = $this->argument('userId') ?? config('services.mtn_momo.collection.user_id');

        // --- 2. Validate Data ---
        if (empty($primaryKey) || empty($baseUrl)) {
            $this->error('MTN_MOMO_COLLECTION_PRIMARY_KEY or MTN_MOMO_BASE_URL is not set in your .env file.');
            return self::FAILURE;
        }

        if (empty($userIdToVerify) || !Uuid::isValid($userIdToVerify)) {
            $this->error("The provided User ID '{$userIdToVerify}' is not a valid UUID.");
            $this->comment('Please provide a valid UUID as an argument or run `php artisan momo:setup-user` first.');
            return self::FAILURE;
        }

        $this->line("Verifying User ID: <fg=yellow>{$userIdToVerify}</>");

        // --- 3. Execute the API Call ---
        try {
            $url = "{$baseUrl}/v1_0/apiuser/{$userIdToVerify}";

            $response = Http::withHeaders([
                'Ocp-Apim-Subscription-Key' => $primaryKey,
            ])->get($url);

            // --- 4. Handle the Response ---
            if ($response->successful()) { // Checks for 2xx status codes
                $this->info('SUCCESS: API User exists and is valid!');
                $this->line('Response:');
                // Use json_encode to pretty-print the JSON response
                $this->line(json_encode($response->json(), JSON_PRETTY_PRINT));
                return self::SUCCESS;
            }

            // Handle failure
            $this->error("FAILED: The API call failed with status code: {$response->status()}");
            $this->line("Response Body: " . ($response->body() ?: '(Empty)'));
            if ($response->status() === 404) {
                $this->comment('This "Not Found" error means the User ID does not exist on MTN\'s server.');
            }
            return self::FAILURE;

        } catch (\Exception $e) {
            $this->error('An exception occurred during the API call:');
            $this->line($e->getMessage());
            return self::FAILURE;
        }
    }
}