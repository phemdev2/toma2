<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('audits', function (Blueprint $table) {
            // Forensic Snapshots
            $table->integer('quantity_before')->nullable()->after('quantity');
            $table->integer('quantity_after')->nullable()->after('quantity_before');
            
            // Financial Snapshots (Capture cost at the exact moment of event)
            $table->decimal('unit_cost_at_time', 12, 2)->nullable()->after('quantity_after');
            $table->decimal('unit_sale_at_time', 12, 2)->nullable()->after('unit_cost_at_time');
            
            // Categorization
            // Types: 'sale', 'restock', 'damage', 'expired', 'theft', 'internal_use', 'audit_adjustment'
            $table->string('event_type')->default('unknown')->index()->after('user_id'); 
            $table->string('reference_id')->nullable()->after('event_type'); // Invoice # or Batch #
        });
    }

    public function down()
    {
        Schema::table('audits', function (Blueprint $table) {
            $table->dropColumn([
                'quantity_before', 
                'quantity_after', 
                'unit_cost_at_time', 
                'unit_sale_at_time', 
                'event_type', 
                'reference_id'
            ]);
        });
    }
};