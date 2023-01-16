<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsPackingZones extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('wms_packing_zones', function (Blueprint $table) {

        $table->integer('reason_code_id')->nullabe();
        $table->integer('product_id')->nullabe();
        $table->integer('real_quanty')->nullabe();

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
