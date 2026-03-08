<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $prefix = config('gdpr.table_prefix', 'gdpr_');

        Schema::create($prefix . 'data_exports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('user_type')->default('App\\Models\\User');
            $table->string('status', 30)->default('pending');
            $table->string('format', 10)->default('json');
            $table->string('file_path')->nullable();
            $table->unsignedBigInteger('file_size_bytes')->nullable();
            $table->string('download_token', 64)->unique()->nullable();
            $table->timestamp('downloaded_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'user_type'], 'gdpr_export_user_idx');
            $table->index('status', 'gdpr_export_status_idx');
            $table->index('download_token', 'gdpr_export_token_idx');
            $table->index('expires_at', 'gdpr_export_expires_idx');
        });
    }

    public function down(): void
    {
        $prefix = config('gdpr.table_prefix', 'gdpr_');
        Schema::dropIfExists($prefix . 'data_exports');
    }
};
