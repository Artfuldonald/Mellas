<?php

namespace App\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class MomoSetupUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature ='momo:setup-user {--callback= :  The providerCallbackHost to register with MTN.}{--force : Run without confirmation.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates and registers a new MTN MoMo API User (UUID)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('--- MTN MoMo API User Setup ---');

        $primaryKey = config('services.mtn_momo.collection.primary_key');
        $baseUrl = rtrim(config('services.mtn_momo.base_url'), '/');

        if (empty($primaryKey) || empty($baseUrl)) {
            $this->error('MTN_MOMO_COLLECTION_PRIMARY_KEY or MTN_MOMO_BASE_URL is not set in your .env file.');
            return self::FAILURE;
        }

        // --- LOGIC FOR GETTING THE CALLBACK HOST ---
        $callbackHost = $this->option('callback');

        if (!$callbackHost) {
            $this->comment("No --callback URL provided. Using APP_URL from .env as the providerCallbackHost.");
            $callbackHost = config('app.url');
        }

        if (empty($callbackHost) || filter_var($callbackHost, FILTER_VALIDATE_URL) === false) {
             $this->error("The providerCallbackHost '{$callbackHost}' is not a valid URL.");
             $this->comment("Please set APP_URL in your .env file or use the --callback option, e.g., --callback=https://webhook.site/your-uuid");
             return self::FAILURE;
        }
        // --- END OF LOGIC ---

        $newUserId = (string) Str::uuid(); 
        $this->line("Generated new API User ID: <fg=yellow>{$newUserId}</>");
        $this->line("Using Callback Host: <fg=yellow>{$callbackHost}</>");


        if (!$this->option('force') && !$this->confirm('Register this UUID with MTN?', true)) {
            $this->comment('Operation cancelled.');
            return self::SUCCESS;
        }

        $this->info('Registering API User with MTN...');

        try {
            $response = Http::withHeaders([
                'X-Reference-Id' => $newUserId,
                'Content-Type' => 'application/json',
                'Ocp-Apim-Subscription-Key' => $primaryKey,
            ])->post("{$baseUrl}/v1_0/apiuser", [
                'providerCallbackHost' => $callbackHost,
            ]);

            if ($response->status() === 201) {
                $this->info('API User successfully registered!');
                $this->updateEnvFile('MTN_MOMO_COLLECTION_USER_ID', $newUserId);               
                $this->updateEnvFile('MTN_MOMO_CALLBACK_HOST', $callbackHost);
                $this->comment('Next: Run `php artisan momo:setup-key`');
                return self::SUCCESS;
            }

            $this->error("Failed to register. Status: {$response->status()}");
            $this->line("Response Body: " . $response->body());
            Log::error('MomoSetupUserCommand failed', ['status' => $response->status(), 'body' => $response->body(), 'sent_callback' => $callbackHost]);
            return self::FAILURE;

        } catch (\Exception $e) {
            $this->error('An exception occurred: ' . $e->getMessage());
            Log::error('MomoSetupUserCommand exception', ['message' => $e->getMessage()]);
            return self::FAILURE;
        }
    }

     private function updateEnvFile(string $key, string $value): void
    {
        $envFilePath = app()->environmentFilePath();
        $content = file_get_contents($envFilePath);

        $oldValue = env($key);

        if ($oldValue && strpos($content, "{$key}=") !== false) {
            $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
            $this->line("Updated <fg=cyan>{$key}</> in your .env file.");
        } else {
            $content .= "\n{$key}={$value}\n";
            $this->line("Added <fg=cyan>{$key}</> to your .env file.");
        }

        file_put_contents($envFilePath, $content);
    }
}
