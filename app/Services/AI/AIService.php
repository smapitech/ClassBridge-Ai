<?php

namespace App\Services\AI;

use App\Models\AIGeneration;
use App\Models\AIProvider;
use App\Models\AISetting;
use App\Models\AIUsageLog;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Core AI Service - resolves providers, enforces limits, logs everything.
 */
class AIService
{
    /** Default educational safety system prompt */
    const SAFETY_PROMPT = "You are an educational assistant for teachers. Generate safe, age-appropriate, classroom-ready learning content. Keep explanations clear, friendly, and suitable for the selected age group. Avoid unsafe, explicit, discriminatory, or harmful content. When teaching children, use simple examples and positive encouragement.";

    /**
     * Generate AI content for a user.
     */
    public function generate(User $user, string $type, array $messages, array $options = []): array
    {
        $schoolId = $user->school_id;

        // 1. Check if AI is enabled for this school
        $settings = $this->getSettings($schoolId);
        if (!$settings->ai_enabled) {
            return $this->failResponse('AI is currently disabled for your school.');
        }
        if ($user->isTeacher() && !$settings->allow_teacher_ai) {
            return $this->failResponse('AI assistant is not available for teachers at this time.');
        }

        // 2. Enforce limits
        $limitCheck = $this->checkLimits($settings, $schoolId);
        if (!$limitCheck['allowed']) {
            return $this->failResponse($limitCheck['message']);
        }

        // 3. Resolve provider
        $providerModel = $this->resolveProvider($settings);
        if (!$providerModel || !$providerModel->isReady()) {
            return $this->failResponse('No active AI provider is configured. Please contact the platform administrator.');
        }

        // 4. Instantiate the provider class
        $provider = $this->makeProvider($providerModel);
        if (!$provider) {
            return $this->failResponse('AI provider could not be initialized.');
        }

        // 5. Prepend safety prompt
        array_unshift($messages, ['role' => 'system', 'content' => self::SAFETY_PROMPT]);

        // 6. Generate (log the attempt atomically)
        $result = $provider->generateText($messages, $options);

        // 7. Calculate cost
        $usage = $result['usage'] ?? [];
        $cost = $provider->estimateCost($usage, $result['model'] ?? null);

        // 8. Log to ai_generations
        AIGeneration::create([
            'school_id' => $schoolId,
            'user_id' => $user->id,
            'provider_id' => $providerModel->id,
            'model' => $result['model'] ?? $provider->getDefaultModel(),
            'type' => $type,
            'title' => $options['title'] ?? null,
            'prompt' => json_encode($messages),
            'response' => $result['success'] ? $result['content'] : null,
            'status' => $result['success'] ? 'completed' : 'failed',
            'error_message' => $result['error'] ?? null,
            'tokens_input' => $usage['input_tokens'] ?? null,
            'tokens_output' => $usage['output_tokens'] ?? null,
            'total_tokens' => (($usage['input_tokens'] ?? 0) + ($usage['output_tokens'] ?? 0)),
            'cost_estimate' => $cost,
            'metadata' => $options['metadata'] ?? null,
        ]);

        // 9. Log to ai_usage_logs
        AIUsageLog::create([
            'school_id' => $schoolId,
            'user_id' => $user->id,
            'provider_id' => $providerModel->id,
            'model' => $result['model'] ?? $provider->getDefaultModel(),
            'type' => $type,
            'tokens_input' => $usage['input_tokens'] ?? null,
            'tokens_output' => $usage['output_tokens'] ?? null,
            'total_tokens' => (($usage['input_tokens'] ?? 0) + ($usage['output_tokens'] ?? 0)),
            'cost_estimate' => $cost,
            'request_status' => $result['success'] ? 'success' : 'failed',
            'error_message' => $result['error'] ?? null,
        ]);

        return [
            'success' => $result['success'],
            'content' => $result['success'] ? $result['content'] : null,
            'error' => $result['error'] ?? null,
            'provider' => $providerModel->name,
            'model' => $result['model'] ?? $provider->getDefaultModel(),
            'usage' => $usage,
            'cost_estimate' => $cost,
        ];
    }

    /**
     * Test a specific provider connection.
     */
    public function testProvider(AIProvider $providerModel): array
    {
        $provider = $this->makeProvider($providerModel);
        if (!$provider) {
            return ['success' => false, 'message' => 'Could not initialize provider.', 'latency_ms' => 0];
        }
        return $provider->testConnection();
    }

    /** Resolve which AI provider to use for a school */
    protected function resolveProvider(?AISetting $settings): ?AIProvider
    {
        // Check school override first
        if ($settings && $settings->allow_school_override && $settings->default_provider_id) {
            $provider = AIProvider::where('id', $settings->default_provider_id)->where('status', 'active')->first();
            if ($provider && $provider->isReady()) {
                return $provider;
            }
        }

        // Fall back to global default
        return AIProvider::active()->default()->first()
            ?? AIProvider::active()->orderBy('name')->first();
    }

    /** Instantiate the correct provider class */
    protected function makeProvider(AIProvider $model): ?AIProviderInterface
    {
        return match ($model->provider_type) {
            'openai' => new OpenAIProvider($model),
            'deepseek' => new DeepSeekProvider($model),
            'custom' => new CustomOpenAICompatibleProvider($model),
            default => new CustomOpenAICompatibleProvider($model),
        };
    }

    /** Get AI settings for a school */
    protected function getSettings(?int $schoolId): AISetting
    {
        return $schoolId ? AISetting::forSchool($schoolId) : AISetting::global() ?? new AISetting();
    }

    /** Check generation/token limits */
    protected function checkLimits(AISetting $settings, ?int $schoolId): array
    {
        if (!$schoolId) return ['allowed' => true, 'message' => ''];

        // Generation count limit
        if ($settings->monthly_generation_limit) {
            $count = AIUsageLog::where('school_id', $schoolId)->success()->thisMonth()->count();
            if ($count >= $settings->monthly_generation_limit) {
                return ['allowed' => false, 'message' => 'Your school has reached the monthly AI generation limit. Please contact your school admin or upgrade your plan.'];
            }
        }

        // Token limit
        if ($settings->monthly_token_limit) {
            $totalTokens = AIUsageLog::where('school_id', $schoolId)->success()->thisMonth()->sum('total_tokens');
            if ($totalTokens >= $settings->monthly_token_limit) {
                return ['allowed' => false, 'message' => 'Your school has reached the monthly AI token limit. Please contact your school admin or upgrade your plan.'];
            }
        }

        // Cost limit
        if ($settings->monthly_cost_limit) {
            $totalCost = AIUsageLog::where('school_id', $schoolId)->success()->thisMonth()->sum('cost_estimate');
            if ($totalCost >= $settings->monthly_cost_limit) {
                return ['allowed' => false, 'message' => 'Your school has reached the monthly AI cost limit. Please contact your school admin or upgrade your plan.'];
            }
        }

        return ['allowed' => true, 'message' => ''];
    }

    protected function failResponse(string $message): array
    {
        return ['success' => false, 'content' => null, 'error' => $message, 'provider' => null, 'model' => null, 'usage' => [], 'cost_estimate' => 0.0];
    }
}