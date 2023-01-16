<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterScheduleType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE wms_schedules MODIFY COLUMN schedule_type ENUM('receipt','deliver','task','stock','transform','unjoin','restock','store','count_detail','validate_adjust','resupply_picking_zone')");
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
