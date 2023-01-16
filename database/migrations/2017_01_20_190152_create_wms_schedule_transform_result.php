<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWmsScheduleTransformResult extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_schedule_transform_result', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('schedule_transform_id')->unsigned();
            $table->integer('schedule_transform_detail_id')->unsigned();
            $table->integer('product_id')->unsigned();
            $table->integer('quanty')->unsigned();
            $table->integer('container_id')->unsigned();
            $table->timestamps();

            $table->foreign('schedule_transform_id')
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
        Schema::drop('wms_schedule_transform_result');
    }
}
