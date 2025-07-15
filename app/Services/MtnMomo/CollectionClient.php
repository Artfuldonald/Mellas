<?php

namespace App\Services\MtnMomo;

use App\Models\MomoToken;
use Carbon\Carbon;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CollectionClient
{
    private string $baseUrl;
    private string $environment;
    private string $currency;
    private string $primaryKey;
    private string $userId;
    private string $apiKey;

    public function __construct()
    {
        $config = config('services.mtn_momo');
        $this->baseUrl = rtrim($config['base_url'], '/');
        $this->environment = $config['environment'];
        $this->currency = $config['currency'];
        $collectionConfig = $config['collection'];
        $this->primaryKey = $collectionConfig['primary_key'];
        $this->userId = $collectionConfig['user_id'];
        $this->apiKey = $collectionConfig['api_key'];
    }

    private function getAccessToken(): ?string
    {
        $token = MomoToken::where('product', 'collection')
            ->where('expires_at', '>', now()->addMinutes(5))
            ->latest()
            ->first();

        if ($token) {
            Log::info('MTN MoMo: Found valid access token in database.');
            return $token->access_token;
        }

        Log::info('MTN MoMo: No valid token in DB. Requesting new access token from MTN.');

        try {
            $response = Http::withBasicAuth($this->userId, $this->apiKey)
                ->withHeaders(['Ocp-Apim-Subscription-Key' => $this->primaryKey])
                ->post("{$this->baseUrl}/collection/token/");

            if (!$response->successful()) {
                Log::error('MTN MoMo [getAccessToken]: Failed to create access token.', [
                    'status' => $response->status(), 'body' => $response->body()
                ]);
                return null;
            }

            $data = $response->json();
            $newToken = MomoToken::create([
                'product' => 'collection',
                'access_token' => $data['access_token'],
                'token_type' => $data['token_type'],
                'expires_at' => now()->addSeconds($data['expires_in'] - 60),
            ]);

            Log::info('MTN MoMo: New access token created and stored successfully.');
            return $newToken->access_token;

        } catch (\Exception $e) {
            Log::critical('MTN MoMo [getAccessToken]: Exception thrown.', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * This is the final, production-ready method for requesting a payment.
     */
    public function requestToPay(string $amount, string $phoneNumber, string $externalId, string $payerMessage, string $payeeNote): array
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return ['success' => false, 'message' => 'Authentication with MTN failed. Could not get access token.'];
        }

        $momoTransactionId = (string) Str::uuid();

        $payload = [
            'amount' => number_format((float)$amount, 2, '.', ''),
            'currency' => $this->currency,
            'externalId' => $externalId,
            'payer' => ['partyIdType' => 'MSISDN', 'partyId' => $phoneNumber],
            'payerMessage' => $payerMessage,
            'payeeNote' => $payeeNote,
        ];

        try {
            $response = Http::withToken($accessToken)
                ->withHeaders([
                    'X-Reference-Id' => $momoTransactionId,
                    'X-Target-Environment' => $this->environment,
                    'Ocp-Apim-Subscription-Key' => $this->primaryKey,
                ])
                ->asJson() // Explicitly set Content-Type to application/json
                ->post($this->baseUrl . '/collection/v1_0/requesttopay', $payload);

            if ($response->status() === 202) {
                Log::info('MTN MoMo: Payment request sent successfully.', [
                    'externalId' => $externalId,
                    'momoTransactionId' => $momoTransactionId,
                ]);
                return [
                    'success' => true,
                    // Return the unique MTN transaction ID so you can save it
                    'momo_reference_id' => $momoTransactionId,
                    'message' => 'Payment request sent.'
                ];
            }

            Log::error('MTN MoMo [requestToPay]: API call failed.', [
                'status' => $response->status(), 'body' => $response->body(), 'payload_sent' => $payload
            ]);
            $errorMessage = $response->json('message') ?? 'The payment gateway returned an error.';
            return ['success' => false, 'message' => $errorMessage, 'error' => $response->json() ?? null];

        } catch (\Exception $e) {
            Log::critical('MTN MoMo [requestToPay]: Exception thrown.', ['message' => $e->getMessage()]);
            return ['success' => false, 'message' => 'An exception occurred while contacting the payment gateway.'];
        }
    }
}