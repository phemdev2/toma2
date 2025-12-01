<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDetailsToStoresTable extends Migration
{
    public function up()
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->string('phone')->after('name'); // Add phone number
            $table->string('email')->nullable()->after('phone'); // Add email
            $table->string('website')->nullable()->after('email'); // Add website
            $table->string('thank_you_message')->default('Thank you for your purchase!')->after('website'); // Add thank you message
            $table->string('visit_again_message')->default('Visit us again!')->after('thank_you_message'); // Add visit again message
        });
    }

    public function down()
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['phone', 'email', 'website', 'thank_you_message', 'visit_again_message']);
        });
    }
}
