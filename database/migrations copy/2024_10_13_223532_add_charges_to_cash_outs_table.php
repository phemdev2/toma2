<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChargesToCashOutsTable extends Migration
{
    public function up()
    {
        Schema::table('cash_outs', function (Blueprint $table) {
            $table->decimal('charges', 10, 2)->default(0); // Default to 0 if charges are not specified
        });
    }

    public function down()
    {
        Schema::table('cash_outs', function (Blueprint $table) {
            $table->dropColumn('charges');
        });
    }
}