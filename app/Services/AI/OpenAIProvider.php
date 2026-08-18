<?php

namespace App\Services\AI;

use App\Models\AIProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * OpenAI chat completions provider (gpt-4o-mini, gpt-4o, etc.).
 */
class OpenAIProvider implements AIProviderInterface
{
    protected ?AIProvider $model;

    public function __construct(AIProvider $model)
    {
        $this->model = $model;
    }

    public function getName(): string
    {
        return $this->model->name;
    }

    public function getDefaultModel(): string
    {
        return $this->model->default_model ?? 'gpt-4o-mini';
    }

    public function supportsStreaming(): bool
    {
        return (bool) $this->model->supports_streaming;
    }

    public function generateText(array $messages, array $options = []): array
    {
        $apiKey = $this->model->getDecryptedApiKey();
        if (!$apiKey) {
            return ['success' => false, 'content' => '', 'usage' => [], 'error' => 'AI provider is not fully configured. Please contact the platform administrator.', 'model' => $this->getDefaultModel()];
        }

        $baseUrl = $this->model->base_url ?: 'https://api.openai.com/v1';
        $model = $options['model'] ?? $this->getDefaultModel();
        $temperature = $options['temperature'] ?? 0.7;
        $maxTokens = $options['max_tokens'] ?? 2048;

        try {
            $response = Http::timeout(60)
                ->withToken($apiKey)
                ->post(rtrim($baseUrl, '/') . '/chat/completions', [
                    'model' => $model,
                    'messages' => $messages,
                    'temperature' => $temperature,
                    'max_tokens' => $maxTokens,
                ]);

            if (!$response->successful()) {
                $error = $response->json('error.message', $response->body());
                Log::error('OpenAI API error', ['status' => $response->status(), 'error' => $error]);
                return [
                    'success' => false,
                    'content' => '',
                    'usage' => [],
                    'error' => 'AI generation failed. Please try again. Error: ' . ($error ?: 'Unknown'),
                    'model' => $model,
                ];
            }

            $data = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? '';
            $usage = [
                'input_tokens' => $data['usage']['prompt_tokens'] ?? 0,
                'output_tokens' => $data['usage']['completion_tokens'] ?? 0,
            ];

            return [
                'success' => true,
                'content' => $content,
                'usage' => $usage,
                'error' => null,
                'model' => $data['model'] ?? $model,
            ];
        } catch (\Exception $e) {
            Log::error('OpenAI request exception', ['message' => $e->getMessage()]);
            return [
                'success' => false,
                'content' => '',
                'usage' => [],
                'error' => 'AI service is temporarily unavailable. Please try again later.',
                'model' => $model,
            ];
        }
    }

    public function estimateCost(array $usage, ?string $model = null): float
    {
        // TODO: Update pricing per https://openai.com/pricing
        $model = $model ?? $this->getDefaultModel();
        $inputTokens = $usage['input_tokens'] ?? 0;
        $outputTokens = $usage['output_tokens'] ?? 0;

        // Approximate pricing (per 1M tokens)
        $pricing = match (true) {
            str_contains($model, 'gpt-4o-mini') => ['input' => 0.15, 'output' => 0.60],
            str_contains($model, 'gpt-4o') => ['input' => 2.50, 'output' => 10.00],
            str_contains($model, 'gpt-3.5') => ['input' => 0.50, 'output' => 1.50],
            default => ['input' => 1.00, 'output' => 4.00],
        };

        $cost = ($inputTokens / 1_000_000 * $pricing['input'])
              + ($outputTokens / 1_000_000 * $pricing['output']);

        return round($cost, 6);
    }

    public function testConnection(): array
    {
        $start = microtime(true);
        $result = $this->generateText([
            ['role' => 'user', 'content' => 'Hi, respond with just "OK" to confirm connectivity.'],
        ], ['max_tokens' => 5, 'temperature' => 0]);
        $latency = (int) ((microtime(true) - $start) * 1000);

        return [
            'success' => $result['success'],
            'message' => $result['success'] ? 'Connection successful!' : ($result['error'] ?? 'Connection failed'),
            'latency_ms' => $latency,
        ];
    }
}