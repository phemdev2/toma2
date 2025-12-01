<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionsTable extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('subscriptions')) {
            Schema::create('subscriptions', function (Blueprint $table) {
                $table->id();

                // Foreign key to users
                $table->foreignId('user_id')->constrained()->onDelete('cascade');

                // Plan information
                $table->string('plan_type'); // e.g., trial, basic, premium
                $table->date('start_date')->nullable();
                $table->date('subscription_expiry_date')->nullable();
                $table->string('plani')->nullable(); // if you have additional metadata

                // Timestamps
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
}
;
