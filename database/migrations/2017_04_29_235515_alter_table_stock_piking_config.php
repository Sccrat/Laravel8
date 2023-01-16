<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableStockPikingConfig extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('wms_stock_picking_config');
        Schema::create('wms_stock_picking_config', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('warehouse_id')->unsigned()->nullable();
            $table->integer('zone_position_id')->unsigned();
            $table->boolean('active')->default(true);  


            $table->foreign('warehouse_id')->references('id')->on('wms_warehouses')->onDelete('cascade');
            $table->foreign('zone_position_id')->references('id')->on('wms_zone_positions')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('wms_stock_picking_config');
    }
}
