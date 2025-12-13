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
    Schema::table('store_inventories', function (Blueprint $table) {
        $table->string('batch_number')->nullable()->after('product_id'); // Add batch_number column
        $table->date('expiry_date')->nullable()->after('batch_number'); // Add expiry_date column
    });
}

public function down()
{
    Schema::table('store_inventories', function (Blueprint $table) {
        $table->dropColumn('batch_number');
        $table->dropColumn('expiry_date');
    });
}
};
