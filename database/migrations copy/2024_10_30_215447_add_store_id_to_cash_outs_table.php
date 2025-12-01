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
            $table->foreignId('store_id')->after('charges')->constrained()->onDelete('cascade');
        });
    }
    
    public function down()
    {
        Schema::table('cash_outs', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropColumn('store_id');
        });
    }
    
};
