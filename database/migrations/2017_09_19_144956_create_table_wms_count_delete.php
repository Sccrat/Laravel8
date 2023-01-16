<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableWmsCountDelete extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('wms_schedule_count_position', function (Blueprint $table) {
      $table->increments('id');
      $table->integer('schedule_id')->unsigned();
      $table->integer('zone_position_id')->unsigned();


      $table->foreign('schedule_id')
      ->references('id')->on('wms_schedules')
      ->onDelete('cascade');
      });
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
