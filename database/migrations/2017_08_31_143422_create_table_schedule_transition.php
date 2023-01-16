<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableScheduleTransition extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('wms_schedule_transition', function (Blueprint $table) {
      $table->increments('id');
      $table->integer('schedule_id')->unsigned();
      $table->integer('transition_id')->unsigned();


      $table->foreign('schedule_id')
      ->references('id')->on('wms_schedules')
      ->onDelete('cascade');

      $table->foreign('transition_id')
      ->references('id')->on('wms_stock_transition')
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
