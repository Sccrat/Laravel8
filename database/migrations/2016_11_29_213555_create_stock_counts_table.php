<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStockCountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_stock_counts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id')->nullable()->unsigned();
            $table->integer('zone_position_id')->nullable()->unsigned();
            $table->integer('code_128_id')->nullable()->unsigned();
            $table->integer('quanty')->nullable()->unsigned();
            $table->integer('schedule_id')->nullable()->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('wms_stock_counts');
    }
}
