<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddShceduleTo128 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('wms_ean_codes128', function (Blueprint $table) {
          //
          $table->integer('schedule_id')->unsigned()->nullable();

          $table->foreign('schedule_id')
              ->references('id')->on('wms_schedules');
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
