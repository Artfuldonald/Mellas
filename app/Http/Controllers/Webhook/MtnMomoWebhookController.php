<?php

namespace App\Http\Controllers\Webhook;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessMomoPayment; // We will create this next

class MtnMomoWebhookController extends Controller
{
    /**
     * Handle incoming MTN MoMo webhook requests.
     */
    public function __invoke(Request $request): JsonResponse
    {
        // === LAYER 1: The Steel Door - Signature Verification ===
        if (!$this->isSignatureValid($request)) {
            Log::warning('MTN MoMo Webhook: Invalid signature.', [
                'ip' => $request->ip(),
                'headers' => $request->headers->all(),
            ]);
            // Abort with 401 Unauthorized. Don't give the attacker any info.
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // At this point, we are 100% sure the request is authentic.
        $payload = $request->json()->all();

        Log::info('MTN MoMo Webhook: Valid signature received.', ['payload' => $payload]);

        // === LAYER 2: The Fallout Shelter - Asynchronous Processing ===
        // We immediately dispatch a job to handle the business logic.
        // This makes our webhook endpoint incredibly fast and reliable.
        // It prevents timeouts if our database or an external service is slow.
        try {
            ProcessMomoPayment::dispatch($payload);
        } catch (\Exception $e) {
            // If the queue fails (e.g., Redis is down), we must log it as critical.
            Log::critical('MTN MoMo Webhook: Failed to dispatch job to queue.', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);
            // Return a 500 error. This tells MTN to retry the webhook later.
            return response()->json(['message' => 'Internal Server Error'], 500);
        }

        // --- Acknowledge Receipt ---
        // Immediately return a 200 OK to MTN to let them know we've
        // received the webhook successfully. If we don't do this,
        // they will keep re-sending it.
        return response()->json(['message' => 'Webhook received and queued for processing.']);
    }

    /**
     * Verify the HMAC-SHA256 signature from the request.
     */
    private function isSignatureValid(Request $request): bool
    {
        $secret = config('services.mtn_momo.collection.api_key');
        if (empty($secret)) {
            Log::error('MTN MoMo Webhook: API Key (secret) is not configured.');
            return false;
        }

        $requestSignature = $request->header('X-Callback-Signature');
        if (empty($requestSignature)) {
            return false;
        }

        // We must use the raw request body, not the parsed JSON.
        $requestBody = $request->getContent();

        $expectedSignature = hash_hmac('sha256', $requestBody, $secret);

        // Use hash_equals for a timing-attack-safe comparison.
        return hash_equals($expectedSignature, $requestSignature);
    }
}