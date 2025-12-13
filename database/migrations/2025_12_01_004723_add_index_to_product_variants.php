<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('store_inventories', function (Blueprint $table) {
        $table->index('product_id'); 
        // Or better, a composite index if you often query by store too:
        // $table->index(['product_id', 'store_id']);
    });
}

public function down()
{
    Schema::table('store_inventories', function (Blueprint $table) {
        $table->dropIndex(['product_id']);
    });
}
};
