<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVinculationType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('wms_personal', function ($t)
      {
        $t->integer('vinculation_type_id')->nullable()->unsigned();
        $t->foreign('vinculation_type_id')
              ->references('id')->on('wms_vinculation_types')
              ->onDelete('cascade');
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
