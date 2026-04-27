<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_agents', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->string('name');
            $table->text('system_prompt')->nullable();
            $table->foreignId('default_provider_id')->nullable()->constrained('providers')->nullOnDelete();
            $table->foreignId('default_provider_model_id')->nullable()->constrained('provider_models')->nullOnDelete();
            $table->unsignedInteger('max_context_tokens')->default(8192);
            $table->unsignedInteger('compaction_threshold_tokens')->default(7000);
            $table->boolean('is_enabled')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_agents');
    }
};
