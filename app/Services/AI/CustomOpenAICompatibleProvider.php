<?php

namespace App\Services\AI;

use App\Models\AIProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Generic OpenAI-compatible provider for custom endpoints.
 * Use this for any API that supports the OpenAI chat/completions format.
 */
class CustomOpenAICompatibleProvider implements AIProviderInterface
{
    protected ?AIProvider $model;

    public function __construct(AIProvider $model)
    {
        $this->model = $model;
    }

    public function getName(): string { return $this->model->name; }
    public function getDefaultModel(): string { return $this->model->default_model ?? 'default'; }
    public function supportsStreaming(): bool { return (bool) $this->model->supports_streaming; }

    public function generateText(array $messages, array $options = []): array
    {
        $apiKey = $this->model->getDecryptedApiKey();
        if (!$apiKey) {
            return [
                'success' => false, 'content' => '', 'usage' => [],
                'error' => 'AI provider is not fully configured. Please contact the platform administrator.',
                'model' => $this->getDefaultModel(),
            ];
        }

        $baseUrl = $this->model->base_url;
        if (!$baseUrl) {
            return ['success' => false, 'content' => '', 'usage' => [], 'error' => 'Provider base URL is not configured.', 'model' => $this->getDefaultModel()];
        }

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
                Log::error('Custom AI API error', ['status' => $response->status(), 'body' => $response->body()]);
                return [
                    'success' => false, 'content' => '', 'usage' => [],
                    'error' => 'AI generation failed. Please try again.',
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
                'success' => true, 'content' => $content, 'usage' => $usage,
                'error' => null, 'model' => $data['model'] ?? $model,
            ];
        } catch (\Exception $e) {
            Log::error('Custom AI request exception', ['message' => $e->getMessage()]);
            return [
                'success' => false, 'content' => '', 'usage' => [],
                'error' => 'AI service is temporarily unavailable.',
                'model' => $model,
            ];
        }
    }

    public function estimateCost(array $usage, ?string $model = null): float
    {
        return 0.0; // Unknown pricing for custom providers
    }

    public function testConnection(): array
    {
        $start = microtime(true);
        $result = $this->generateText([
            ['role' => 'user', 'content' => 'Hi, respond with just "OK".'],
        ], ['max_tokens' => 5, 'temperature' => 0]);
        $latency = (int) ((microtime(true) - $start) * 1000);

        return [
            'success' => $result['success'],
            'message' => $result['success'] ? 'Connection successful!' : ($result['error'] ?? 'Connection failed'),
            'latency_ms' => $latency,
        ];
    }
}