<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_endpoints', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('mode', ['relay', 'managed']);
            $table->string('destination_url');
            $table->foreignUuid('provider_id')->nullable()->constrained()->nullOnDelete();
            $table->text('provider_secret')->nullable();
            $table->string('ingest_token', 64)->unique();
            $table->text('signing_secret');
            $table->enum('status', ['active', 'paused', 'disabled'])->default('active');
            $table->timestamps();

            $table->index(['project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_endpoints');
    }
};
