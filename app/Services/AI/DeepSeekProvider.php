<?php

namespace App\Services\AI;

use App\Models\AIProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * DeepSeek AI provider - OpenAI-compatible API.
 * Base URL, API key, and model names are configurable from Super Admin dashboard.
 */
class DeepSeekProvider implements AIProviderInterface
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
        return $this->model->default_model ?? 'deepseek-v4-flash';
    }

    public function supportsStreaming(): bool
    {
        return (bool) $this->model->supports_streaming;
    }

    public function generateText(array $messages, array $options = []): array
    {
        $apiKey = $this->model->getDecryptedApiKey();
        if (!$apiKey) {
            return [
                'success' => false,
                'content' => '',
                'usage' => [],
                'error' => 'AI provider is not fully configured. Please contact the platform administrator.',
                'model' => $this->getDefaultModel(),
            ];
        }

        // DeepSeek uses an OpenAI-compatible API structure
        $baseUrl = $this->model->base_url ?: 'https://api.deepseek.com/v1';
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
                Log::error('DeepSeek API error', ['status' => $response->status(), 'error' => $error]);
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
            Log::error('DeepSeek request exception', ['message' => $e->getMessage()]);
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
        // TODO: Update with actual DeepSeek pricing
        // Current approximate: https://api-docs.deepseek.com/quick_start/pricing
        $model = $model ?? $this->getDefaultModel();
        $inputTokens = $usage['input_tokens'] ?? 0;
        $outputTokens = $usage['output_tokens'] ?? 0;

        // DeepSeek approximate pricing per 1M tokens (USD)
        $pricing = match (true) {
            str_contains($model, 'deepseek-chat') => ['input' => 0.14, 'output' => 0.28],
            str_contains($model, 'deepseek-reasoner') => ['input' => 0.55, 'output' => 2.19],
            str_contains($model, 'deepseek-v4') => ['input' => 0.20, 'output' => 0.40],
            default => ['input' => 0.20, 'output' => 0.40],
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