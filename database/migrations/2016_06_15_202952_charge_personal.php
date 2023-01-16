<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChargePersonal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('wms_personal', function (Blueprint $table) {
        $table->integer('charge_id')->unsigned();

        //foreign
        $table->foreign('charge_id')->references('id')->on('wms_charges');
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
