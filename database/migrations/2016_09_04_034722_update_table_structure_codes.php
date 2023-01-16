<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTableStructureCodes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('wms_structure_codes', function ($table) {
        $table->dropForeign('wms_structure_codes_packing_type_id_foreign');
        $table->dropColumn('packing_type_id');
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
