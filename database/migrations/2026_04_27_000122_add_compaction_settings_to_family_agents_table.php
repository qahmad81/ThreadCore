<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('family_agents', function (Blueprint $table) {
            $table->foreignId('compaction_provider_id')->nullable()->after('default_provider_model_id')->constrained('providers')->nullOnDelete();
            $table->foreignId('compaction_provider_model_id')->nullable()->after('compaction_provider_id')->constrained('provider_models')->nullOnDelete();
            $table->text('compaction_prompt')->nullable()->after('compaction_threshold_tokens');
        });
    }

    public function down(): void
    {
        Schema::table('family_agents', function (Blueprint $table) {
            $table->dropForeign(['compaction_provider_id']);
            $table->dropForeign(['compaction_provider_model_id']);
            $table->dropColumn(['compaction_provider_id', 'compaction_provider_model_id', 'compaction_prompt']);
        });
    }
};
