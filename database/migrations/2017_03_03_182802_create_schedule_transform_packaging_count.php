<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScheduleTransformPackagingCount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_schedule_transform_packaging_count', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('schedule_transform_id')->unsigned();
            $table->integer('schedule_id')->unsigned();
            $table->integer('count_quanty')->unsigned();
            $table->integer('count_index')->unsigned();

            $table->timestamps();

            $table->foreign('schedule_transform_id','fk_count_packaging')
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
        Schema::drop('wms_schedule_transform_packaging_count');
    }
}
