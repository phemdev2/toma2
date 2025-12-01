<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCashOutsTable extends Migration
{
    public function up()
    {
        Schema::create('cash_outs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->decimal('charges', 10, 2)->default(0);
            $table->string('payment_method')->default('withdraw');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cash_outs');
    }
}
