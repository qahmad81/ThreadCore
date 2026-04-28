<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('thread_messages', function (Blueprint $table) {
            $table->decimal('cost', 12, 6)->default(0)->after('output_tokens');
        });

        Schema::table('provider_models', function (Blueprint $table) {
            $table->json('pricing')->nullable()->after('metadata');
        });
    }

    public function down(): void
    {
        Schema::table('provider_models', function (Blueprint $table) {
            $table->dropColumn('pricing');
        });

        Schema::table('thread_messages', function (Blueprint $table) {
            $table->dropColumn('cost');
        });
    }
};
