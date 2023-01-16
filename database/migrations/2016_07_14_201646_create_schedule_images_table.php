<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScheduleImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_schedule_images', function (Blueprint $table) {
          $table->increments('id');
          $table->integer('schedule_id')->unsigned();
          $table->string('url', 200)->nullable();

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
        Schema::drop('wms_schedule_images');
    }
}
