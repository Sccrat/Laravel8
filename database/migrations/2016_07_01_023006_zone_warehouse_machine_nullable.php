<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ZoneWarehouseMachineNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('wms_machines', function($table)
      {
        $table->dropForeign('wms_machines_zone_id_foreign');
        $table->dropColumn('zone_id');
        $table->integer('distribution_center_id')->unsigned()->nullable();
        $table->integer('warehouse_id')->unsigned()->nullable();

        $table->foreign('distribution_center_id')
              ->references('id')->on('wms_distribution_centers')
              ->onDelete('cascade');

        $table->foreign('warehouse_id')
              ->references('id')->on('wms_warehouses')
              ->onDelete('cascade');

        // $table->integer('zone_id')->nullable()->change();
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
