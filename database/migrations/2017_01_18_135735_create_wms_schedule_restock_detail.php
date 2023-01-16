<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWmsScheduleRestockDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_schedule_restock_detail', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('schedule_restock_id')->unsigned();
            $table->integer('product_id')->unsigned();
            $table->integer('position_id')->unsigned()->nullable();
            $table->integer('code128_id')->unsigned()->nullable();
            $table->integer('code14_id')->unsigned()->nullable();
            $table->integer('quanty')->unsigned();

            $table->enum('detail_status', ['pendding', 'removed','relocate']);

            $table->foreign('schedule_restock_id')
                ->references('id')->on('wms_schedule_restock')
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
        Schema::drop('wms_schedule_restock_detail');
    }
}
