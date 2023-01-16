<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableInventaryInformation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_inventary_information', function (Blueprint $table) {

      $table->increments('id');
      $table->integer('warehouse_id');
      $table->integer('client_id');
      $table->integer('product_sub_types');
      $table->integer('product_id');
      $table->integer('schedule_id');
     

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
