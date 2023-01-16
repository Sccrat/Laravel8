<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScheduleTransformResultPackaging extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_schedule_transform_result_packaging', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('schedule_id')->unsigned();
            $table->integer('schedule_transform_id')->unsigned();
            $table->integer('product_id')->unsigned();
            $table->integer('quanty')->unsigned();
            $table->integer('container_id')->unsigned();
            $table->boolean('have_code')->default(false);  
            $table->integer('ean14_id')->unsigned();
            $table->string('status', 50);
            $table->timestamps();

            $table->foreign('schedule_transform_id','fk_result_packaging_1')
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
        Schema::drop('wms_schedule_transform_result_packaging');
    }
}
