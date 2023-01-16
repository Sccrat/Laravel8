<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScheduleTransformDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_schedule_transform_detail', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('schedule_transform_id')->unsigned();
            $table->integer('product_id')->unsigned();
            $table->integer('position_id')->unsigned()->nullable();
            $table->integer('code128_id')->unsigned()->nullable();
            $table->integer('code14_id')->unsigned()->nullable();
            $table->integer('quanty')->unsigned();

            $table->enum('detail_type', ['receipt', 'storage']);
            $table->enum('detail_status', ['pendding', 'removed','transformed']);

            $table->foreign('schedule_transform_id')
                ->references('id')->on('wms_schedule_transform')
                ->onDelete('cascade');
            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('wms_schedule_transform_detail');
    }
}
