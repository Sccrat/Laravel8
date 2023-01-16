<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScheduleOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_schedule_orders', function (Blueprint $table) {
          $table->increments('id');
          $table->integer('schedule_id')->unsigned();
          $table->integer('order_id')->unsigned();

          $table->foreign('schedule_id')->references('id')->on('wms_schedules')->onDelete('cascade');

          $table->foreign('order_id')->references('id')->on('wms_orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('schedule_orders');
    }
}
