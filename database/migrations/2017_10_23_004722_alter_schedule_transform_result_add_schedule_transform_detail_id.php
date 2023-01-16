<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterScheduleTransformResultAddScheduleTransformDetailId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wms_schedule_transform_result', function (Blueprint $table) {
            $table->integer('schedule_transform_detail_id')->nullable()->unsigned();

            $table->foreign('schedule_transform_detail_id','sche_transf_r_sche_transf_d_id_foreign')->references('id')->on('wms_schedule_transform_detail');
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
