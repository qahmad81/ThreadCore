<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gateway_request_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_account_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('api_key_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('thread_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('provider_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('provider_model_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('ok');
            $table->unsignedInteger('input_tokens')->default(0);
            $table->unsignedInteger('output_tokens')->default(0);
            $table->json('request_payload')->nullable();
            $table->json('response_metadata')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gateway_request_logs');
    }
};
