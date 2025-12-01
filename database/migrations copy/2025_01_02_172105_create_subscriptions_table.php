<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionsTable extends Migration
{
    public function up()
{
    Schema::create('subscriptions', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->enum('plan_type', ['trial', 'basic', 'premium'])->default('trial');
        $table->timestamp('trial_start_date')->useCurrent();
        $table->timestamps();

        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    });
}


    public function down()
    {
        Schema::dropIfExists('subscriptions');
    }
}

