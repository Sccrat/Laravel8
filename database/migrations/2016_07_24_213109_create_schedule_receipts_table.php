<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScheduleReceiptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_schedule_receipts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('schedule_id')->unsigned();
            $table->string('officer_name');
            $table->string('city');
            $table->string('officer_phone');
            $table->string('driver_name');
            $table->string('driver_identification');
            $table->string('driver_phone');
            $table->string('vehicle_plate');
            $table->string('company');
            $table->string('company_phone');
            $table->integer('receipt_type_id')->unsigned();
            $table->integer('warehouse_id')->unsigned()->nullable();
            $table->integer('zone_id')->unsigned()->nullable();
            $table->string('seal', 50)->nullable();
            $table->string('officer', 50)->nullable();
            $table->integer('responsible_id')->unsigned()->nullable();

            $table->foreign('responsible_id')->references('id')->on('wms_personal')->onDelete('cascade');

            $table->foreign('warehouse_id')->references('id')->on('wms_warehouses')->onDelete('cascade');

            $table->foreign('zone_id')->references('id')->on('wms_zones')->onDelete('cascade');

            $table->foreign('receipt_type_id')->references('id')->on('wms_receipt_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('wms_schedule_receipts');
    }
}
