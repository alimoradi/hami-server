<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAvailableHoursTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('available_hours', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('time_from', 5);
            $table->string('time_to', 5);
            $table->integer('provider_id');
            $table->boolean('expired')->default(false);
            $table->boolean('repeat')->default(true);
            $table->smallInteger('repeating_day_of_week')->nullable();
            $table->boolean('disabled')->default(false);
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
        Schema::dropIfExists('available_hours');
    }
}
