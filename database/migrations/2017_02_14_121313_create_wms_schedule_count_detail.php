<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWmsScheduleCountDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_schedule_count_detail', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('schedule_id')->unsigned();
            $table->integer('product_id')->unsigned();
            $table->integer('count')->unsigned();
            $table->integer('document')->unsigned();
            


            $table->foreign('product_id')
                ->references('id')->on('wms_products')
                ->onDelete('cascade');            


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
        Schema::drop('wms_schedule_count_detail');
    }
}
