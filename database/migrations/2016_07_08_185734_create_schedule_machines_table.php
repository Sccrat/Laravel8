<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScheduleMachinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_schedule_machines', function (Blueprint $table) {
          $table->increments('id');
          $table->integer('schedule_id')->unsigned();
          $table->integer('machine_id')->unsigned();

          $table->foreign('schedule_id')->references('id')->on('wms_schedules')->onDelete('cascade');

          $table->foreign('machine_id')->references('id')->on('wms_machines')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('schedule_machines');
    }
}
