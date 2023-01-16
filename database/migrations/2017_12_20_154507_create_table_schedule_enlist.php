<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableScheduleEnlist extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('wms_schedule_enlist', function (Blueprint $table) {
        $table->increments('id');
        $table->integer('schedule_id');
        $table->integer('user_id');
        $table->char('name',50);
        $table->datetime('start_date');
        $table->datetime('end_date');
        $table->enum('status', ['process', 'relocated','removed']);




        // $table->foreign('schedule_id')->references('id')->on('wms_schedules');

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
