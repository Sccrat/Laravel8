<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScheduleTransformTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_schedule_transform', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('schedule_id')->unsigned();

            $table->boolean('remove_status')->default(false);  
            $table->boolean('transform_status')->default(false);  
            $table->boolean('status')->default(false);  

            $table->foreign('schedule_id')
                ->references('id')->on('wms_schedules')
                ->onDelete('cascade');
            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('wms_schedule_transform');
    }
}
