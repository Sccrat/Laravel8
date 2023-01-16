<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ShceduleModification extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('wms_schedules', function($table)
      {
        $table->dropForeign('wms_schedules_warehouse_id_foreign');
        $table->dropForeign('wms_schedules_zone_id_foreign');
        $table->dropForeign('wms_schedules_responsible_id_foreign');
        //$table->dropIndex(['warehouse_id', 'zone_id', 'responsible_id']);
        $table->dropColumn(['warehouse_id', 'zone_id', 'responsible_id', 'seal', 'officer']);

        $table->enum('schedule_type', ['receipt', 'deliver']);
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
