<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    // php artisan migrate:refresh --path=database/migrations/2026_03_23_150029_create_ring_central_messages_table.php
    // php artisan migrate:rollback --path=database/migrations/2026_03_23_150029_create_ring_central_messages_table.php
    // php artisan migrate --path=database/migrations/2026_03_23_150029_create_ring_central_messages_table.php

    public function up(): void
    {
        Schema::create('ring_central_messages', function (Blueprint $table) {
            $table->id();

            // Unique identifier for the record
            $table->uuid('uuid')->unique();

            // Relationships
            $table->string('ringcentral_account_uuid');   // which RingCentral account sent/received this
            $table->string('company_uuid');
            $table->string('created_by_uuid'); // optional if system-generated

            // RingCentral message fields
            $table->string('to');                    // destination phone number
            $table->string('from');                  // sender phone number
            $table->text('text');                    // message content (RingCentral uses "text")

            $table->string('ringcentral_message_id') // Original ID from RingCentral API
                ->unique()
                ->nullable();                        // nullable in case of draft/outgoing before sending

            $table->string('status');                // e.g. 'queued', 'sent', 'delivered', 'failed', etc.

            // Extra data
            $table->json('metadata')->nullable();

            // Timestamps
            $table->timestamps();

            // Indexes for performance
            $table->index('company_uuid');
            $table->index('created_by_uuid');

            // Composite index for from + to (useful for conversations)
            $table->index(['from', 'to'], 'rc_msg_from_to_idx');

            // Composite index for account + ringcentral message id
            $table->index(
                ['ringcentral_account_uuid', 'ringcentral_message_id'],
                'rc_msg_account_msg_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ring_central_messages');
    }
};
