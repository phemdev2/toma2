<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserIdAndShopIdToOrderItemsTable extends Migration
{
    public function up()
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(); // Add user_id
            $table->unsignedBigInteger('shop_id')->nullable(); // Add shop_id

            // Optional: add foreign key constraints if you have users and shops tables
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // $table->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['shop_id']);
            $table->dropColumn(['user_id', 'shop_id']);
        });
    }
}