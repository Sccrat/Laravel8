<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableScheduleCountBoxes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('wms_picking_count_schedule_boxes', function (Blueprint $table) {
          $table->increments('id');
          $table->integer('schedule_id')->unsigned();
          $table->integer('code14_id')->unsigned();
          $table->integer('product_id')->unsigned();
          $table->integer('quanty')->unsigned();

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
