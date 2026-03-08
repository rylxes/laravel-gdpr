<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $prefix = config('gdpr.table_prefix', 'gdpr_');

        Schema::create($prefix . 'erasure_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('user_type')->default('App\\Models\\User');
            $table->string('status', 30)->default('pending');
            $table->string('strategy', 30)->default('anonymize');
            $table->string('reason')->nullable();
            $table->string('requested_by')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'user_type'], 'gdpr_erasure_user_idx');
            $table->index('status', 'gdpr_erasure_status_idx');
            $table->index('scheduled_at', 'gdpr_erasure_scheduled_idx');
        });
    }

    public function down(): void
    {
        $prefix = config('gdpr.table_prefix', 'gdpr_');
        Schema::dropIfExists($prefix . 'erasure_requests');
    }
};
