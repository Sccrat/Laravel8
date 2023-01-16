<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWmsColors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_colors', function (Blueprint $table) {
            $table->increments('id');
            $table->string('alternative_id',50);
            $table->string('name',50);
            $table->string('alternative_name',50);
            $table->string('group_id',50);
            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('wms_colors');
    }
}
