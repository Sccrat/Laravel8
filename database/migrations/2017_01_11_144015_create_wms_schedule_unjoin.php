<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWmsScheduleUnjoin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_schedule_unjoin', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('schedule_id')->unsigned();
            $table->integer('warehouse_id')->unsigned()->nullable();

            $table->boolean('remove_status')->default(false);  
            $table->boolean('unjoin_status')->default(false);  
            $table->boolean('store_status')->default(false);  
            $table->boolean('status')->default(false);  

            $table->foreign('schedule_id')
                ->references('id')->on('wms_schedules')
                ->onDelete('cascade');
            $table->foreign('warehouse_id')->references('id')->on('wms_warehouses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('wms_schedule_unjoin');
    }
}
