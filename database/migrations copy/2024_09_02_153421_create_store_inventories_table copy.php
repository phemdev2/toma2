<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreInventoriesTable extends Migration
{
    public function up()
{
    Schema::create('store_inventories', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('store_id');
        $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
        $table->unsignedBigInteger('product_id');
        $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade'); // Added foreign key
        $table->integer('quantity');
        $table->timestamps();
    });
}


    public function down()
    {
        Schema::dropIfExists('store_inventories');
    }
}

