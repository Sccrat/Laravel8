<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeNameShceduleLenght extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      DB::statement('ALTER TABLE `wms_schedules` MODIFY COLUMN `name` VARCHAR(200)');
      // Schema::table('wms_schedules', function ($table) {
      //     $table->string('name', 200)->change();
      // });
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
