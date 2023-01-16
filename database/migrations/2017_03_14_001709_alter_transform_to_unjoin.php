<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTransformToUnjoin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wms_schedule_transform', function (Blueprint $table) {
            //
            $table->string('type_transform', 50)->default("transform");
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
            $table->dropColumn('type_transform')->default("transform");
        });
    }
}
