<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWmsSizes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_sizes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('alternative_id',50);
            $table->string('name',50);
            $table->string('alternative_name',50);
            $table->string('alternative_name2',50);
            $table->string('order',50);
            $table->enum('size_type', ['main', 'complement'])->default('main');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('wms_sizes');
    }
}
