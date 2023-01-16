<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableWmsPickingCount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('wms_picking_count', function (Blueprint $table) {
          $table->increments('id');
          $table->integer('schedule_id')->unsigned();
          $table->integer('count1')->unsigned();
          $table->integer('count2')->unsigned();

          $table->foreign('schedule_id')
          ->references('id')->on('wms_schedules')
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
