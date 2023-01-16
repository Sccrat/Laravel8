<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveUnityTypesIaCodes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('wms_container_types', function ($table) {
        $table->dropColumn('is_unidad_empaque');
        $table->dropColumn('is_unidad_logistica');
        //$table->dropForeign('wms_container_types_code_ia_foreign');
        $table->dropColumn('ia_code_id');
        $table->enum('packaging_type', array('logistica', 'empaque'));
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
