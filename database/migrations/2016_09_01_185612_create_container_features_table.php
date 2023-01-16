<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContainerFeaturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_container_features', function (Blueprint $table) {
          $table->increments('id');
          $table->integer('feature_id')->unsigned();
          $table->integer('container_id')->unsigned();
          $table->double('value', 15, 6)->default(0);

          $table->foreign('feature_id')
                ->references('id')->on('wms_features')
                ->onDelete('cascade');

          $table->foreign('container_id')
                ->references('id')->on('wms_containers')
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
        Schema::drop('wms_container_features');
    }
}
