<?php namespace App\Services\Payment;

interface PaymentGatewayInterface
{
    /** Initialize a transaction and return [success => bool, authorization_url => string, reference => string, error => string] */
    public function initializeTransaction(array $params): array;

    /** Verify a transaction by reference. Returns [success => bool, amount => float, status => string, reference => string, error => string] */
    public function verifyTransaction(string $reference): array;

    /** Process webhook payload. Returns [success => bool, event => string, reference => string, data => array] */
    public function processWebhook(array $payload, string $signature): array;

    /** Human-readable gateway name */
    public function getGatewayName(): string;
}