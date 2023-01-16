<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStoreStatusWmsScheduleTransform extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wms_schedule_transform', function (Blueprint $table) {
            $table->boolean('store_status')->default(false);  
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wms_schedule_transform', function (Blueprint $table) {
            //
        });
    }
}
