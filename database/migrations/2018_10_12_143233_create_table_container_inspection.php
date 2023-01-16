<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableContainerInspection extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_container_inspection', function (Blueprint $table) {
        $table->increments('id');
        $table->string('close_status')->nullable();
        $table->boolean('closing_mechanism')->nullable();
        $table->boolean('container_plate')->nullable();
        $table->boolean('container_status')->nullable();
        $table->string('container_types')->nullable();        
        $table->boolean('door_status')->nullable();
        $table->boolean('equal_number')->nullable();
        $table->boolean('equal_seal')->nullable();
        $table->boolean('floor')->nullable();
        $table->string('hinge')->nullable();
        $table->string('observation')->nullable();
        $table->boolean('paint')->nullable();
        $table->boolean('roof')->nullable();
        $table->boolean('seal_stamp')->nullable();
        $table->boolean('seal_status')->nullable();
        $table->boolean('wall')->nullable();
        $table->string('container_number')->nullable();
        $table->integer('user_id')->nullable();
        $table->string('specify')->nullable();

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
