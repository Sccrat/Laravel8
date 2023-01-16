<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScheduleTransformDetailResult extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_schedule_transform_detail_result', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('schedule_id')->unsigned();
            $table->integer('schedule_transform_detail_id')->unsigned();
            $table->integer('product_id')->unsigned();
            $table->integer('quanty')->unsigned();

            $table->foreign('schedule_transform_detail_id','fk_schedule_transform_detail')
                ->references('id')->on('wms_schedule_transform_detail')
                ->onDelete('cascade');

            $table->foreign('product_id')
                ->references('id')->on('wms_products')
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
        Schema::drop('wms_schedule_transform_detail_result');
    }
}
