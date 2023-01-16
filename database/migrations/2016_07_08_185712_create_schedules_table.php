<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_schedules', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamp('start_date');
            $table->timestamp('end_date');
            $table->string('name', 50)->nullable();
            $table->integer('warehouse_id')->unsigned()->nullable();
            $table->integer('zone_id')->unsigned()->nullable();
            $table->timestamps();

            $table->foreign('warehouse_id')->references('id')->on('wms_warehouses')->onDelete('cascade');

            $table->foreign('zone_id')->references('id')->on('wms_zones')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('schedules');
    }
}
