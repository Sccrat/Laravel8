<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWarehouseScheduleTransform extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wms_schedule_transform', function (Blueprint $table) {
            $table->integer('warehouse_id')->unsigned()->nullable();
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
        Schema::table('wms_schedule_transform', function (Blueprint $table) {
            //
        });
    }
}
