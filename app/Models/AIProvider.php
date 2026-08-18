<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class AIProvider extends Model
{
    protected $table = 'ai_providers';

    protected $fillable = [
        'name', 'slug', 'provider_type', 'status', 'is_default',
        'base_url', 'api_key', 'default_model', 'available_models',
        'supports_chat', 'supports_streaming', 'supports_embeddings',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'available_models' => 'array',
            'settings' => 'array',
            'supports_chat' => 'boolean',
            'supports_streaming' => 'boolean',
            'supports_embeddings' => 'boolean',
        ];
    }

    /** Get decrypted API key */
    public function getDecryptedApiKey(): ?string
    {
        if (empty($this->api_key)) {
            return null;
        }
        try {
            return Crypt::decryptString($this->api_key);
        } catch (\Exception) {
            return null;
        }
    }

    /** Set API key, auto-encrypt if not empty */
    public function setApiKeyAttribute(?string $value): void
    {
        if (empty($value)) {
            $this->attributes['api_key'] = null;
        } else {
            $this->attributes['api_key'] = Crypt::encryptString($value);
        }
    }

    /** Get masked API key for display */
    public function maskedApiKey(): string
    {
        $key = $this->getDecryptedApiKey();
        if (!$key) return 'Not set';
        if (strlen($key) <= 8) return str_repeat('*', strlen($key));
        return substr($key, 0, 4) . str_repeat('*', max(0, strlen($key) - 8)) . substr($key, -4);
    }

    /** Check if provider has valid API key */
    public function hasApiKey(): bool
    {
        return !empty($this->getDecryptedApiKey());
    }

    /** Check if provider is ready for use */
    public function isReady(): bool
    {
        return $this->status === 'active' && $this->hasApiKey();
    }

    public function scopeActive($q) { return $q->where('status', 'active'); }
    public function scopeDefault($q) { return $q->where('is_default', true); }

    public function generations(): HasMany { return $this->hasMany(AIGeneration::class, 'provider_id'); }
    public function usageLogs(): HasMany { return $this->hasMany(AIUsageLog::class, 'provider_id'); }
}