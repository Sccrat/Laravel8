<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStockMovementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_stock_movements', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id')->unsigned()->nullable();
            $table->string('product_reference',200)->nullable();
            $table->string('product_ean',500)->nullable();
            $table->integer('product_quanty')->unsigned()->nullable();
            $table->string('zone_position_code',100);
            $table->string('code128',500)->nullable();
            $table->string('code14',500)->nullable();
            $table->string('username',50);
            $table->integer('warehouse_id')->unsigned();
            $table->enum('action', ['income', 'output']);
            $table->enum('concept', ['storage', 'relocate', 'transform', 'adjustment', 'dispatch', 'pickin']);
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
        Schema::drop('wms_stock_movements');
    }
}
