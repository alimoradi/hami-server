<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSessionCallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('session_calls', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->bigInteger('caller_id');
            $table->bigInteger('receptor_id');
            $table->bigInteger('session_id');
            $table->uuid('caller_access_token');
            $table->uuid('receptor_access_token');
            $table->integer('max_duration');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
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
        Schema::dropIfExists('session_calls');
    }
}
