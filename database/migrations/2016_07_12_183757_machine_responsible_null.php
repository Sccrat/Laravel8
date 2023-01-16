<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MachineResponsibleNull extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      //ALTER TABLE `wms_machines`	CHANGE COLUMN `responsable_id` `responsable_id` INT(10) UNSIGNED NULL AFTER `machine_type_id`
      DB::statement('ALTER TABLE `wms_machines`	CHANGE COLUMN `responsable_id` `responsable_id` INT(10) UNSIGNED NULL AFTER `machine_type_id`');
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
