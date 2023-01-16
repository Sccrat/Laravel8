<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterScheduleTransformDetailDetailstatusToReprocess extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      DB::statement("ALTER TABLE wms_schedule_transform_detail MODIFY COLUMN detail_status ENUM('pendding','removed','positioned','transformed','to_reprocess','reprocessing','stored')");      
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
