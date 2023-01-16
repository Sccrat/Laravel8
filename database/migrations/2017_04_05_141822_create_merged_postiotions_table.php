<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMergedPostiotionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_merged_positions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code');
            $table->integer('from_position_id')->unsigned();
            $table->integer('to_position_id')->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('wms_merged_positions');
    }
}
