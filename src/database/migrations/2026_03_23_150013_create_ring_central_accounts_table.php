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
        Schema::create('ring_central_accounts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('company_uuid');
            $table->string('created_by_uuid');
            $table->string('client_id');
            $table->string('client_secret')->nullable(); // can be stored encrypted
            $table->string('server_url')->default('https://platform.ringcentral.com');
            $table->text('refresh_token')->nullable();   // encrypted
            $table->string('access_token')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('extension_id')->nullable();  // e.g. '~' for default
            $table->string('phone_number')->nullable();
            $table->string('friendly_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
            // $table->softDeletes(); // optional

            $table->index('company_uuid');
            $table->index('created_by_uuid');
        });

        Schema::table('ring_central_accounts', function (Blueprint $table) {
            $table->unique(
                ['company_uuid', 'client_id', 'client_secret', 'phone_number'],
                'ring_central_accounts_company_client_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ring_central_accounts', function (Blueprint $table) {
            $table->dropUnique('ring_central_accounts_company_client_unique');
        });

        Schema::dropIfExists('ring_central_accounts');
    }
};
