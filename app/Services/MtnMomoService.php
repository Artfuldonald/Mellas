<?php
// app/Services/MtnMomoService.php

namespace App\Services;

use Illuminate\Support\Facades\Http; // Laravel's built-in HTTP Client
use Illuminate\Support\Facades\Cache; // For caching the access token
use Illuminate\Support\Facades\Log;   // For logging errors/info
use Illuminate\Support\Str;          // For generating UUIDs
use Exception;                       // For throwing exceptions

class MtnMomoService
{
    protected string $baseUrl;
    protected string $apiUserId;
    protected string $apiKey;
    protected string $subscriptionKey;
    protected string $callbackUrl;
    protected string $currency;
    protected string $environment;
    protected string $tokenCacheKey = 'mtn_momo_access_token'; // Cache key for the token

    /**
     * Constructor - Load configuration.
     */
    public function __construct()
    {
        // Load configuration securely from config/services.php (which reads from .env)
        $config = config('services.mtn_momo');

        $this->baseUrl = rtrim($config['base_uri'] ?? '', '/'); // Remove trailing slash if present
        $this->apiUserId = $config['api_user_id'] ?? null;
        $this->apiKey = $config['api_key'] ?? null;
        $this->subscriptionKey = $config['subscription_key'] ?? null;
        $this->callbackUrl = $config['callback_url'] ?? null;
        $this->currency = $config['currency'] ?? 'GHS';
        $this->environment = $config['environment'] ?? 'sandbox';

        // Validate essential configuration
        if (!$this->baseUrl || !$this->apiUserId || !$this->apiKey || !$this->subscriptionKey) {
            Log::error('MTN MoMo Service configuration is incomplete. Please check config/services.php and .env file.');
            // Throwing an exception might be better in a real app to halt execution
            // throw new Exception('MTN MoMo Service configuration is incomplete.');
        }
    }

    /**
     * Get a valid OAuth 2.0 Access Token from MTN MoMo API.
     * Handles caching to avoid requesting a new token on every request.
     *
     * @return string|null The access token or null on failure.
     */
    protected function getAccessToken(): ?string
    {
        // Try to get the token from cache first
        $cachedToken = Cache::get($this->tokenCacheKey);
        if ($cachedToken) {
            // Log::debug('Using cached MTN MoMo token.'); // Optional debug log
            return $cachedToken;
        }

        Log::info('Requesting new MTN MoMo access token.');

        // --- Prepare Basic Auth Credentials ---
        // Base64 encode "APIUserID:APIKey"
        $credentials = base64_encode("{$this->apiUserId}:{$this->apiKey}");

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $credentials,
                'Ocp-Apim-Subscription-Key' => $this->subscriptionKey,
                'Content-Type' => 'application/json', // Often needed for POST
            ])
            // Adjust endpoint based on environment/product if needed (e.g., /collection/token/)
            // Check MTN Docs for the correct token endpoint for the 'Collection' product
            ->post("{$this->baseUrl}/collection/token/"); // <<< VERIFY THIS TOKEN ENDPOINT in MTN Docs

            if ($response->successful() && $response->json('access_token')) {
                $token = $response->json('access_token');
                $expiresIn = $response->json('expires_in'); // Seconds until expiry

                // Cache the token for slightly less than its expiry time (e.g., 5 minutes buffer)
                $cacheDuration = max(60, $expiresIn - 300); // Cache for at least 60s, or expiry minus 5 mins
                Cache::put($this->tokenCacheKey, $token, $cacheDuration);

                Log::info('Successfully obtained and cached new MTN MoMo access token.');
                return $token;
            } else {
                Log::error('Failed to get MTN MoMo access token.', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Exception while getting MTN MoMo access token: ' . $e->getMessage(), [
                 'exception' => $e
            ]);
            return null;
        }
    }

    /**
     * Initiate a Request to Pay transaction.
     *
     * @param string $amount The amount to charge.
     * @param string $customerMsisdn The customer's phone number (e.g., '233xxxxxxxx'). Check format required by MTN.
     * @param string $externalId Your unique reference ID for this transaction (e.g., Order Number or a new UUID).
     * @param string $payerMessage Message shown to the payer on approval screen.
     * @param string $payeeNote Message for the payee (you).
     * @return array{status: bool, message: string, reference_id: string|null, mtn_transaction_id: null} Result array.
     */
    public function requestToPay(string $amount, string $customerMsisdn, string $externalId, string $payerMessage = 'Payment for order', string $payeeNote = 'Order Payment'): array
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return ['status' => false, 'message' => 'Failed to authenticate with MTN MoMo.', 'reference_id' => $externalId, 'mtn_transaction_id' => null];
        }

        // Generate a unique reference ID for *this specific API request* (MTN requires this in header)
        $requestReferenceId = (string) Str::uuid();

        $payload = [
            'amount' => $amount,
            'currency' => $this->currency,
            'externalId' => $externalId, // Your reference for the overall transaction
            'payer' => [
                'partyIdType' => 'MSISDN', // Standard identifier type for phone number
                'partyId' => $customerMsisdn,
            ],
            'payerMessage' => $payerMessage,
            'payeeNote' => $payeeNote,
        ];

        Log::info("Initiating MTN MoMo Request to Pay for externalId: {$externalId}", $payload);

        try {
            $response = Http::withToken($accessToken) // Use the obtained Bearer token
                ->withHeaders([
                    'X-Reference-Id' => $requestReferenceId, // Unique ID for THIS API call
                    'X-Target-Environment' => $this->environment, // 'sandbox' or 'production'
                    'Ocp-Apim-Subscription-Key' => $this->subscriptionKey,
                    'Content-Type' => 'application/json',
                    'Cache-Control' => 'no-cache', // Often recommended
                ])
                // The callback URL is crucial for getting status updates
                ->withOptions(['json' => $payload]) // Send data as JSON body
                // <<< VERIFY THIS /requesttopay ENDPOINT in MTN Docs for Collection product
                ->post("{$this->baseUrl}/collection/v1_0/requesttopay"); // Use v1_0 or v2_0 as per docs

            // MTN MoMo typically returns 202 Accepted for successful initiation
            if ($response->status() === 202) {
                Log::info("MTN MoMo Request to Pay initiated successfully for externalId: {$externalId}. Request Reference ID: {$requestReferenceId}. Waiting for callback.");
                // The actual success/failure comes via webhook.
                // We return success here meaning the *initiation* was accepted by MTN.
                return ['status' => true, 'message' => 'Payment request initiated successfully. Waiting for customer approval.', 'reference_id' => $externalId, 'mtn_transaction_id' => null];
            } else {
                // Handle specific MTN error codes if possible based on their documentation
                Log::error('MTN MoMo Request to Pay initiation failed.', [
                    'externalId' => $externalId,
                    'requestReferenceId' => $requestReferenceId,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                 $errorMessage = 'Failed to initiate payment request with MTN MoMo.';
                 // Try to parse error message from response if available
                 $responseData = $response->json();
                 if (isset($responseData['message'])) {
                     $errorMessage .= ' Reason: ' . $responseData['message'];
                 } elseif (isset($responseData['code'])) {
                      $errorMessage .= ' Code: ' . $responseData['code'];
                 }

                return ['status' => false, 'message' => $errorMessage, 'reference_id' => $externalId, 'mtn_transaction_id' => null];
            }
        } catch (\Exception $e) {
             Log::error('Exception during MTN MoMo Request to Pay initiation: ' . $e->getMessage(), [
                 'externalId' => $externalId,
                 'requestReferenceId' => $requestReferenceId,
                 'exception' => $e
            ]);
            return ['status' => false, 'message' => 'An unexpected error occurred while initiating payment.', 'reference_id' => $externalId, 'mtn_transaction_id' => null];
        }
    }

    /**
     * Check the status of a previously initiated Request to Pay transaction.
     * (Useful if callback fails or for manual checks)
     *
     * @param string $requestReferenceId The UUID used in the 'X-Reference-Id' header of the POST /requesttopay call.
     * @return array|null Transaction details or null on failure.
     */
    public function getRequestToPayStatus(string $requestReferenceId): ?array
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            Log::error('Cannot get transaction status: Failed to authenticate with MTN MoMo.');
            return null;
        }

        Log::info("Checking MTN MoMo transaction status for Request Reference ID: {$requestReferenceId}");

        try {
             $response = Http::withToken($accessToken)
                ->withHeaders([
                    'X-Target-Environment' => $this->environment,
                    'Ocp-Apim-Subscription-Key' => $this->subscriptionKey,
                    'Cache-Control' => 'no-cache',
                ])
                 // <<< VERIFY THIS /requesttopay/{referenceId} ENDPOINT in MTN Docs
                ->get("{$this->baseUrl}/collection/v1_0/requesttopay/{$requestReferenceId}");

            if ($response->successful()) {
                $data = $response->json();
                Log::info("MTN MoMo status check successful for Request Reference ID: {$requestReferenceId}", $data);
                // Return the full response data (includes amount, status, externalId, payer, reason etc.)
                return $data;
            } else {
                 Log::error('MTN MoMo status check failed.', [
                    'requestReferenceId' => $requestReferenceId,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                return null;
            }

        } catch (\Exception $e) {
             Log::error('Exception during MTN MoMo status check: ' . $e->getMessage(), [
                 'requestReferenceId' => $requestReferenceId,
                 'exception' => $e
            ]);
            return null;
        }
    }

    // TODO: Add methods for other MTN MoMo API calls if needed (e.g., Transfer, Get Balance, Validate Account Holder)
}