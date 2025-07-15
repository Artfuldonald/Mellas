<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Client\PendingRequest;

class MtnMomoService
{
    private $baseUrl;
    private $environment;
    private $primaryKey;
    private $userId;
    private $apiKey;
    private $currency;

    public function __construct()
    {
        $config = config('services.mtn_momo');

        $required = [
            'base_url', 'environment', 'currency',
            'collection.primary_key', 'collection.user_id', 'collection.api_key'
        ];

        foreach ($required as $key) {
            if (!data_get($config, $key)) {
                throw new \InvalidArgumentException("MTN MoMo config missing: {$key}");
            }
        }

        $this->baseUrl = rtrim($config['base_url'], '/');
        $this->environment = $config['environment'];
        $this->primaryKey = $config['collection']['primary_key'];
        $this->userId = $config['collection']['user_id'];
        $this->apiKey = $config['collection']['api_key'];
        $this->currency = $config['currency'];
    }
        

    /**
     * Create or retrieve a cached access token.
     */
    private function getAccessToken(): ?string
    {
        // Cache the token to avoid requesting a new one for every API call.
        return Cache::remember('mtn_momo_collection_token', 3500, function () {
            Log::info('MTN MoMo: Requesting new access token.');
            $response = Http::withBasicAuth($this->userId, $this->apiKey)
                ->withHeaders(['Ocp-Apim-Subscription-Key' => $this->primaryKey])
                ->post($this->baseUrl . '/collection/token/');

            if ($response->successful()) {
                return $response->json()['access_token'];
            }

            Log::error('MTN MoMo: Failed to create access token', [
                'status' => $response->status(), 'response' => $response->body()
            ]);
            return null;
        });
    }

    /**
     * Build the authenticated HTTP client.
     */
    private function client(): ?PendingRequest
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return null;
        }

        return Http::withToken($accessToken)
            ->withHeaders([
                'X-Target-Environment' => $this->environment,
                'Ocp-Apim-Subscription-Key' => $this->primaryKey,
                'Content-Type' => 'application/json',
            ]);
    }

    /**
     * Request a payment from a customer.
     *
     * @param string $amount
     * @param string $phoneNumber The customer's phone number (e.g., 46733123453)
     * @param string $externalId Your unique order/transaction ID
     * @param string $payerMessage Message shown to the customer
     * @param string $payeeNote Note for your records
     */
    public function requestToPay(string $amount, string $phoneNumber, string $externalId, string $payerMessage, string $payeeNote): array
    {
        $client = $this->client();
        if (!$client) {
            return ['success' => false, 'message' => 'Authentication failed.'];
        }

        $momoReferenceId = (string) Str::uuid();

        $payload = [
            'amount' => $amount,
            'currency' => $this->currency, // Use currency from config
            'externalId' => $externalId,
            'payer' => [
                'partyIdType' => 'MSISDN',
                'partyId' => $phoneNumber,
            ],
            'payerMessage' => $payerMessage,
            'payeeNote' => $payeeNote,
        ];

        // The webhook URL is configured in the MTN Developer Portal.
        // You should NOT send the X-Callback-Url header unless you have a specific
        // reason to override the portal setting for a single request.

        try {
            $response = $client
                ->withHeaders(['X-Reference-Id' => $momoReferenceId])
                ->post($this->baseUrl . '/collection/v1_0/requesttopay', $payload);

            // 202 Accepted is the success status for this request
            if ($response->status() === 202) {
                Log::info('MTN MoMo: Payment request sent successfully.', ['externalId' => $externalId]);
                return [
                    'success' => true,
                    'momo_reference_id' => $momoReferenceId,
                    'message' => 'Payment request sent. Awaiting user confirmation.'
                ];
            }

            Log::error('MTN MoMo: Payment request failed.', [
                'status' => $response->status(), 'response' => $response->body(), 'payload' => $payload
            ]);
            return [
                'success' => false,
                'message' => 'Payment request failed.', 'error' => $response->json() ?? $response->body()
            ];

        } catch (\Exception $e) {
            Log::error('MTN MoMo: Exception during payment request.', [
                'message' => $e->getMessage(), 'payload' => $payload
            ]);
            return ['success' => false, 'message' => 'An exception occurred.'];
        }
    }

    /**
     * Check payment status
     */
    public function getPaymentStatus(string $referenceId): array
    {
        $accessToken = $this->createAccessToken();
        
        if (!$accessToken) {
            return [
                'success' => false,
                'message' => 'Failed to authenticate with MTN MoMo'
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'X-Target-Environment' => $this->environment,
                'Ocp-Apim-Subscription-Key' => $this->primaryKey,
            ])->get($this->baseUrl . '/collection/v1_0/requesttopay/' . $referenceId);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'status' => $data['status'], 
                    'data' => $data
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to get payment status'
            ];

        } catch (\Exception $e) {
            Log::error('MTN MoMo: Exception getting payment status', [
                'reference_id' => $referenceId,
                'message' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to get payment status: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Format phone number for MTN MoMo (remove country code if present)
     */
    private function formatPhoneNumber(string $phone): string
    {
        // Remove any non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        
        if (str_starts_with($phone, '233')) {
            $phone = substr($phone, 3);
        }        
        
        if (!str_starts_with($phone, '0')) {
            $phone = '0' . $phone;
        }
        
        return $phone;
    }

    /**
     * Validate phone number format
     */
    public function isValidPhoneNumber(string $phone): bool
    {
        $formatted = $this->formatPhoneNumber($phone);
        
        // Ghana mobile numbers: 0XX XXXX XXX (10 digits starting with 0)
        return preg_match('/^0[2-9][0-9]{8}$/', $formatted);
    }
}
