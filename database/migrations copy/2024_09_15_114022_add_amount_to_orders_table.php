<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
   public function up()
{
    Schema::table('orders', function (Blueprint $table) {
        $table->decimal('amount', 10, 2)->after('order_date')->nullable(); // Add amount column
    });
}

public function down()
{
    Schema::table('orders', function (Blueprint $table) {
        $table->dropColumn('amount');
    });
}

    
};
