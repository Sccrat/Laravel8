<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMachineFeaturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_machine_features', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('feature_id')->unsigned();
            $table->integer('machine_id')->unsigned();
            $table->double('value', 15, 6)->default(0);

            $table->foreign('feature_id')
                  ->references('id')->on('wms_features')
                  ->onDelete('cascade');

            $table->foreign('machine_id')
                  ->references('id')->on('wms_machines')
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
        Schema::drop('wms_machine_features');
    }
}
