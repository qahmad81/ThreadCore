<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('threads', function (Blueprint $table) {
            $table->foreignId('customer_account_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('api_key_id')->nullable()->after('customer_account_id')->constrained()->nullOnDelete();
        });

        Schema::table('thread_messages', function (Blueprint $table) {
            $table->boolean('is_forgotten')->default(false)->after('is_memory');
        });
    }

    public function down(): void
    {
        Schema::table('thread_messages', function (Blueprint $table) {
            $table->dropColumn('is_forgotten');
        });

        Schema::table('threads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('api_key_id');
            $table->dropConstrainedForeignId('customer_account_id');
        });
    }
};
