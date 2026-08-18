<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('provider_type')->default('openai'); // openai, deepseek, custom
            $table->string('status')->default('inactive');       // active, inactive
            $table->boolean('is_default')->default(false);
            $table->string('base_url')->nullable();
            $table->text('api_key')->nullable();                 // encrypted
            $table->string('default_model')->nullable();
            $table->json('available_models')->nullable();
            $table->boolean('supports_chat')->default(true);
            $table->boolean('supports_streaming')->default(false);
            $table->boolean('supports_embeddings')->default(false);
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_providers');
    }
};