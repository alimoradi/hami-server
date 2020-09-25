<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSessionReferralsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('session_referrals', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('session_id');
            $table->text("note")->nullable()->default(null);
            $table->boolean('referrer_id')->default(false);
            $table->boolean('refund_confirmed')->default(false);
            $table->integer('surveyor_id')->nullable();
            $table->timestamp('surveyed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('session_referrals');
    }
}
