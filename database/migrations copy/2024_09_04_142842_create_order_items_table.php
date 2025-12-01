<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id(); // Primary key for order items
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade'); // Foreign key referencing 'orders' table
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade'); // Foreign key referencing 'products' table
            $table->foreignId('product_variants_id')->nullable()->constrained('product_variants')->onDelete('set null'); // Foreign key referencing 'product_variants' table, nullable
            $table->integer('quantity'); // Quantity of the product or variant
            $table->decimal('price', 8, 2); // Price of the product or variant
            $table->timestamps(); // Created and updated timestamps
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_items');
    }
}
