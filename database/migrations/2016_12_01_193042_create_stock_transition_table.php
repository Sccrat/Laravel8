<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStockTransitionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_stock_transition', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id')->nullable()->unsigned();
            $table->integer('zone_position_id')->nullable()->unsigned();
            $table->integer('code128_id')->nullable()->unsigned();
            $table->integer('code14_id')->nullable()->unsigned();
            $table->integer('quanty')->nullable()->unsigned();
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
        Schema::drop('wms_stock_transition');
    }
}
