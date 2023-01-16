<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusVacation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE wms_personal MODIFY COLUMN status ENUM('active', 'inactive', 'inabilited', 'vacations')");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      DB::statement("ALTER TABLE wms_personal MODIFY COLUMN status ENUM('active', 'inactive', 'inabilited')");
    }
}
