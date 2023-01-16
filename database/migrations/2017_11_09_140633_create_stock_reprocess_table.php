<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStockReprocessTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('wms_stock_reprocess', function (Blueprint $table) {
        $table->increments('id');
        $table->integer('reprocess_type_id')->unsigned();
        $table->integer('product_id')->unsigned()->nullable();
        $table->integer('quanty')->unsigned();
        $table->integer('code14_id')->unsigned()->nullable();
        $table->integer('position_source_id')->unsigned();        
        $table->integer('schedule_source_id')->unsigned();
        $table->integer('schedule_target_id')->unsigned()->nullable();
        
        $table->foreign('reprocess_type_id')->references('id')->on('wms_reprocess_types');
        $table->foreign('product_id')->references('id')->on('wms_products');
        $table->foreign('code14_id')->references('id')->on('wms_ean_codes14');
        $table->foreign('position_source_id')->references('id')->on('wms_zone_positions');
        $table->foreign('schedule_source_id')->references('id')->on('wms_schedules');
        $table->foreign('schedule_target_id')->references('id')->on('wms_schedules');
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
