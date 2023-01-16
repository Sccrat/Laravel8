<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterScheduleTransformResult extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wms_schedule_transform_result', function (Blueprint $table) {
            $table->dropColumn('schedule_transform_detail_id');
            $table->dropColumn('container_id');
            $table->integer('transform_task_id')->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wms_schedule_transform_result', function (Blueprint $table) {
            //
        });
    }
}
