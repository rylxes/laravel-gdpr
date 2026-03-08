<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $prefix = config('gdpr.table_prefix', 'gdpr_');

        Schema::create($prefix . 'consent_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('user_type')->default('App\\Models\\User');
            $table->string('consent_type', 100);
            $table->string('consent_version', 50);
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('given_at');
            $table->timestamp('revoked_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'user_type'], 'gdpr_consent_user_idx');
            $table->index(['consent_type', 'consent_version'], 'gdpr_consent_type_ver_idx');
            $table->index('given_at', 'gdpr_consent_given_idx');
        });
    }

    public function down(): void
    {
        $prefix = config('gdpr.table_prefix', 'gdpr_');
        Schema::dropIfExists($prefix . 'consent_logs');
    }
};
