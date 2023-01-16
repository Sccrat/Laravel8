<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSeveralReasoncodesTypesEnum extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

 DB::statement("ALTER TABLE wms_reason_codes MODIFY COLUMN type ENUM('type_receipt',
'type_transform',
'type_codes',
'type_stock',
'type_return',
'type_departure',
'type_prepare',
'type_storage',
'type_picking',
'type_relocate')");
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
