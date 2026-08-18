<?php namespace App\Services\Payment;

use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Paystack payment gateway implementation.
 * Supports initializeTransaction, verifyTransaction, and webhook processing.
 */
class PaystackPaymentService implements PaymentGatewayInterface
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = $this->getMode() === 'live'
            ? 'https://api.paystack.co'
            : 'https://api.paystack.co';
    }

    public function getGatewayName(): string { return 'Paystack'; }

    /** Get secret key from DB platform_settings, fallback to .env */
    protected function getSecretKey(): string
    {
        $dbKey = PlatformSetting::get('paystack_secret_key');
        if ($dbKey) {
            try { return Crypt::decryptString($dbKey); } catch (\Exception) {}
        }
        return env('PAYSTACK_SECRET_KEY', '');
    }

    /** Get public key */
    protected function getPublicKey(): string
    {
        return PlatformSetting::get('paystack_public_key') ?: env('PAYSTACK_PUBLIC_KEY', '');
    }

    /** Get webhook secret for signature validation */
    protected function getWebhookSecret(): string
    {
        return PlatformSetting::get('paystack_webhook_secret') ?: env('PAYSTACK_WEBHOOK_SECRET', '');
    }

    /** Get mode: test or live */
    protected function getMode(): string
    {
        return PlatformSetting::get('paystack_mode') ?: env('PAYSTACK_MODE', 'test');
    }

    /**
     * Initialize a Paystack transaction.
     * @param array $params [email, amount, reference, callback_url, metadata]
     */
    public function initializeTransaction(array $params): array
    {
        $secretKey = $this->getSecretKey();
        if (empty($secretKey)) {
            return ['success' => false, 'authorization_url' => '', 'reference' => '', 'error' => 'Paystack secret key is not configured.'];
        }

        // Amount must be in kobo (or smallest currency unit)
        $amountInKobo = (int) round(($params['amount'] ?? 0) * 100);

        try {
            $response = Http::withToken($secretKey)
                ->timeout(30)
                ->post($this->baseUrl . '/transaction/initialize', [
                    'email' => $params['email'],
                    'amount' => $amountInKobo,
                    'reference' => $params['reference'],
                    'callback_url' => $params['callback_url'],
                    'currency' => $params['currency'] ?? 'USD',
                    'metadata' => $params['metadata'] ?? [],
                ]);

            $data = $response->json();

            if (!($data['status'] ?? false)) {
                Log::error('Paystack initialize failed', ['response' => $data]);
                return [
                    'success' => false,
                    'authorization_url' => '',
                    'reference' => $params['reference'],
                    'error' => $data['message'] ?? 'Payment initialization failed.',
                ];
            }

            return [
                'success' => true,
                'authorization_url' => $data['data']['authorization_url'],
                'reference' => $data['data']['reference'],
                'error' => null,
            ];
        } catch (\Exception $e) {
            Log::error('Paystack initialize exception', ['message' => $e->getMessage()]);
            return [
                'success' => false,
                'authorization_url' => '',
                'reference' => $params['reference'],
                'error' => 'Payment service is temporarily unavailable.',
            ];
        }
    }

    /**
     * Verify a Paystack transaction by reference.
     */
    public function verifyTransaction(string $reference): array
    {
        $secretKey = $this->getSecretKey();
        if (empty($secretKey)) {
            return ['success' => false, 'amount' => 0, 'status' => '', 'reference' => $reference, 'error' => 'Paystack secret key is not configured.'];
        }

        try {
            $response = Http::withToken($secretKey)
                ->timeout(30)
                ->get($this->baseUrl . '/transaction/verify/' . $reference);

            $data = $response->json();

            if (!($data['status'] ?? false)) {
                return [
                    'success' => false, 'amount' => 0, 'status' => 'failed',
                    'reference' => $reference, 'error' => $data['message'] ?? 'Verification failed.',
                ];
            }

            $txData = $data['data'];
            $isSuccessful = ($txData['status'] ?? '') === 'success';
            $amount = ($txData['amount'] ?? 0) / 100; // Convert from kobo

            return [
                'success' => $isSuccessful,
                'amount' => $amount,
                'status' => $isSuccessful ? 'successful' : 'failed',
                'reference' => $txData['reference'],
                'error' => $isSuccessful ? null : 'Transaction was not successful.',
                'gateway_response' => $txData,
            ];
        } catch (\Exception $e) {
            Log::error('Paystack verify exception', ['message' => $e->getMessage()]);
            return [
                'success' => false, 'amount' => 0, 'status' => 'error',
                'reference' => $reference, 'error' => 'Could not verify transaction.',
            ];
        }
    }

    /**
     * Process incoming Paystack webhook.
     */
    public function processWebhook(array $payload, string $signature): array
    {
        // Validate signature
        $webhookSecret = $this->getWebhookSecret();
        if (!empty($webhookSecret)) {
            $computedSignature = hash_hmac('sha512', json_encode($payload), $webhookSecret);
            if (!hash_equals($computedSignature, $signature)) {
                Log::warning('Paystack webhook: invalid signature');
                return ['success' => false, 'event' => '', 'reference' => '', 'data' => []];
            }
        }

        $event = $payload['event'] ?? '';
        $data = $payload['data'] ?? [];
        $reference = $data['reference'] ?? '';

        Log::info('Paystack webhook received', ['event' => $event, 'reference' => $reference]);

        return [
            'success' => true,
            'event' => $event,
            'reference' => $reference,
            'data' => $data,
        ];
    }
}