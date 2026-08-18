<?php

namespace App\Services\AI;

/**
 * Contract for AI provider implementations.
 * Add new providers (Gemini, Claude, Grok) by implementing this interface.
 */
interface AIProviderInterface
{
    /**
     * Send a chat completion request and return the response.
     *
     * @param array $messages Array of ['role' => 'system'|'user'|'assistant', 'content' => string]
     * @param array $options  Optional overrides: model, temperature, max_tokens, stream
     * @return array ['success' => bool, 'content' => string, 'usage' => ['input_tokens' => int, 'output_tokens' => int], 'error' => string|null, 'model' => string]
     */
    public function generateText(array $messages, array $options = []): array;

    /** Human-readable provider name */
    public function getName(): string;

    /** Returns the default model for this provider */
    public function getDefaultModel(): string;

    /** Whether this provider supports SSE streaming */
    public function supportsStreaming(): bool;

    /**
     * Estimate USD cost for the given token usage.
     * TODO: Update pricing values per provider when rates change.
     */
    public function estimateCost(array $usage, ?string $model = null): float;

    /** Test connection with a small prompt. Returns ['success' => bool, 'message' => string, 'latency_ms' => int] */
    public function testConnection(): array;
}