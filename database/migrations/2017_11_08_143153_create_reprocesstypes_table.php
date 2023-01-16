<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReprocesstypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('wms_reprocess_types', function (Blueprint $table) {
        $table->increments('id');
        $table->string('name', 100);       
        $table->integer('zone_type_id')->unsigned();
      
        $table->foreign('zone_type_id')
              ->references('id')->on('wms_zone_types');
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
