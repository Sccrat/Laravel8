<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePositionData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_schedule_position_packing', function (Blueprint $table) {

        $table->increments('id');
        $table->integer('zone_position_id');
        $table->integer('schedule_id');
        $table->integer('real_quanty');
        $table->integer('product_id');
        $table->integer('code14_id');
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
