<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdditionalInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('additional_info', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string("national_code", 10)->nullable();
            $table->string("land_line_number")->nullable();
            $table->string("address")->nullable();
            $table->string("postal_code",10)->nullable();
            $table->bigInteger("user_id");
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
        Schema::dropIfExists('additional_info');
    }
}
