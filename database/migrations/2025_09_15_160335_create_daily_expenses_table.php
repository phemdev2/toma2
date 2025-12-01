<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('daily_expenses')) {
            Schema::create('daily_expenses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('daily_record_id')->constrained()->onDelete('cascade'); // FK to daily_records
                $table->string('item');
                $table->decimal('amount', 10, 2);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_expenses');
    }
};
