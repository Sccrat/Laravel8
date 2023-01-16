<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSheduleTransformActionColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE wms_schedules MODIFY COLUMN schedule_type ENUM('receipt','deliver','task', 'stock', 'transform')");

        Schema::table('wms_schedules', function (Blueprint $table) {

            $table->string('schedule_action',50);
            // $table->enum('schedule_action', ['receipt', 'deliver']);

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
