<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ScheduleNameText extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      // Schema::table('wms_schedules', function (Blueprint $table) {
      //     $table->text('name')->change();
      // });
      DB::statement('ALTER TABLE wms_schedules MODIFY name TEXT');
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
