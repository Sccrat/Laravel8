<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScheduleTransformValidateAdjust extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_schedule_transform_validate_adjust', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('schedule_id')->unsigned();
            $table->integer('schedule_transform_result_packaging_id')->unsigned();
      
      
            $table->foreign('schedule_id')
            ->references('id')->on('wms_schedules')
            ->onDelete('cascade');
      
            $table->foreign('schedule_transform_result_packaging_id', 'transform_validate_adjust_transform_result_packaging')
            ->references('id')->on('wms_schedule_transform_result_packaging')
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
