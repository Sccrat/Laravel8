<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeColumnScheduleSamples extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::table('wms_samples', function ($table) {
        //     $table->integer('schedule_id',10)->nullable()->change();
        // });
        // DB::statement("ALTER TABLE wms_samples MODIFY COLUMN  schedule_id integer NULL");
        Schema::table('wms_samples', function ($table) {
             $table->dropForeign('wms_samples_ibfk_2');
            $table->dropColumn('schedule_id');
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
