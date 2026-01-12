<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('audits', function (Blueprint $table) {
            $table->id();
            
            // Relationships
            $table->unsignedBigInteger('product_id')->index();
            $table->unsignedBigInteger('store_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            
            // Categorization (The Pro features)
            // Types: 'restock', 'sale', 'damage', 'theft', 'audit_adjustment', etc.
            $table->string('event_type')->default('unknown')->index(); 
            $table->string('action')->nullable(); // Legacy support (optional)
            $table->string('reference_id')->nullable(); // Invoice # or Batch ID
            
            // Quantities & Snapshots (Forensic Trail)
            $table->integer('quantity'); // The change amount (+10 or -5)
            $table->integer('quantity_before')->nullable(); // Snapshot before change
            $table->integer('quantity_after')->nullable();  // Snapshot after change
            
            // Financial Snapshots (Value at moment of transaction)
            $table->decimal('unit_cost_at_time', 12, 2)->nullable();
            $table->decimal('unit_sale_at_time', 12, 2)->nullable();
            
            // Inventory Details
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();

            // Optional: Foreign Key Constraints (Uncomment if your tables enforce strict mode)
            // $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            // $table->foreign('store_id')->references('id')->on('stores')->onDelete('set null');
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('audits');
    }
};