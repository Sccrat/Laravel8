<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableContainerInspectionCloseHandles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_close_handles', function (Blueprint $table) {
        $table->increments('id');
        $table->string('close_handles')->nullable();
        $table->integer('container_inspection_id')->nullable();
        

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
