<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('cash_outs', function (Blueprint $table) {
            $table->string('payment_method')->default('withdraw'); // Default to 'withdraw'
        });
    }
    
    public function down()
    {
        Schema::table('cash_outs', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });
    }
    
};
