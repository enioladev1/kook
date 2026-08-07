<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('webhook_endpoint_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('project_id')->constrained()->cascadeOnDelete();
            $table->string('idempotency_key')->nullable();
            $table->jsonb('headers');
            $table->jsonb('payload');
            $table->text('raw_body');
            $table->boolean('signature_valid')->nullable();
            $table->enum('status', ['pending', 'processing', 'success', 'failed'])->default('pending');
            $table->timestamp('received_at');
            $table->timestamps();

            $table->index(['webhook_endpoint_id', 'created_at']);
            $table->index(['project_id', 'created_at']);
            $table->index('status');
        });

        // Idempotency must only be enforced when a key is present, so this is
        // a partial unique index rather than a plain composite unique index.
        DB::statement(
            'CREATE UNIQUE INDEX webhook_events_endpoint_idempotency_unique '.
            'ON webhook_events (webhook_endpoint_id, idempotency_key) '.
            'WHERE idempotency_key IS NOT NULL'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_events');
    }
};
