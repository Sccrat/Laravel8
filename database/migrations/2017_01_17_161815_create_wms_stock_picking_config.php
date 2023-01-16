<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWmsStockPickingConfig extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_stock_picking_config', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id')->unsigned();
            $table->integer('zone_id')->unsigned();
            $table->integer('min_stock')->unsigned();
            $table->integer('save_stock')->unsigned();

            $table->boolean('active')->default(true);  

            $table->foreign('product_id')
                  ->references('id')->on('wms_products')
                  ->onDelete('cascade');

            $table->foreign('zone_id')
                  ->references('id')->on('wms_zones')
                  ->onDelete('cascade');

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
