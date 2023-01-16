<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScheduleTransformResultTotal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_schedule_transform_result_total', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('schedule_transform_id')->unsigned();
            $table->integer('product_id')->unsigned();
            $table->integer('quanty')->unsigned();
            $table->integer('container_id')->unsigned();
            $table->integer('quanty_packaging')->unsigned();
            $table->timestamps();

            $table->foreign('schedule_transform_id','fk_reult_total_transform')
                ->references('id')->on('wms_schedule_transform')
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
        Schema::drop('wms_schedule_transform_result_total');
    }
}
