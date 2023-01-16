<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScheduleStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_schedule_stocks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('inventory_type', 10);
            $table->integer('schedule_id')->unsigned();
            $table->integer('client_id')->unsigned()->nullable();
            $table->integer('product_type_id')->unsigned()->nullable();
            $table->integer('warehouse_id')->unsigned()->nullable();
            $table->string('reference', 50)->nullable();

            //references
            $table->foreign('schedule_id')->references('id')->on('wms_schedules')->onDelete('cascade');
            $table->foreign('client_id')->references('id')->on('wms_clients')->onDelete('cascade');
            $table->foreign('product_type_id')->references('id')->on('wms_product_types')->onDelete('cascade');
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
        Schema::drop('wms_schedule_stocks');
    }
}
