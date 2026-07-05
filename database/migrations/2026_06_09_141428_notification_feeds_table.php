<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        {
        Schema::create('notification_feeds', function (Blueprint $table): void {
            $table->id();
            $table
                ->foreignId('recipient_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('recipient_role', 32);
            $table->string('kind', 32);
            $table->string('status', 32);
            $table->string('reference', 64);
            $table->string('title', 191);
            $table->text('body');
            $table->json('payload')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(
                ['recipient_role', 'recipient_user_id', 'created_at'],
                'notif_feed_recipient_created_idx',
            );
            $table->index(['recipient_role', 'status'], 'notif_feed_role_status_idx');
            $table->unique(
                ['recipient_role', 'recipient_user_id', 'kind', 'reference', 'status'],
                'notification_feeds_unique',
            );
        });
    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_feeds');
    }
};
