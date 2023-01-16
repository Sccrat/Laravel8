<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableStockPickingConfigProduct extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('wms_stock_picking_config_product', function (Blueprint $table) {
      $table->increments('id');
      $table->integer('stock_picking_config_id')->unsigned();
      $table->integer('product_id')->unsigned();


      $table->foreign('stock_picking_config_id')
      ->references('id')->on('wms_stock_picking_config')
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
        //
    }
}
