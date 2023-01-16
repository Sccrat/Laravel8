<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScheduleDispatch extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

      Schema::create('wms_schedule_dispatch', function (Blueprint $table) {

      $table->increments('id');
      $table->integer('schedule_id');
      $table->string('city');
      $table->string('driver_name');
      $table->string('driver_identification');
      $table->string('driver_phone');
      $table->string('vehicle_plate');
      $table->string('company');
      $table->string('company_phone');
      $table->integer('responsible_id');
      $table->integer('warehouse_id');
      $table->string('seal');

    //   , 'driver_name', 'driver_identification', 'driver_phone', 'vehicle_plate', 'company', 'company_phone'responsible_id

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
