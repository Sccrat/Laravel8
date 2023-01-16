<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GroupIdPersonalNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::table('wms_personal', function ($table)
        // {
        //     $table->integer('group_id')->nullable()->change();
        // });
        DB::statement('ALTER TABLE `wms_personal` MODIFY `group_id` INTEGER UNSIGNED NULL;');
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
