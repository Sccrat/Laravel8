<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContentIndicatorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('wms_content_indicators', function (Blueprint $table) {
          $table->increments('id');
          $table->smallInteger('content_indicator')->unsigned();//Indicador del contenido
          $table->integer('quanty')->unsigned()->nullable(); //Cantidad contenido
          $table->integer('product_id')->unsigned();
          $table->integer('container_id')->unsigned();
          $table->timestamps();
          $table->foreign('product_id')
          ->references('id')->on('wms_products')
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
        //
    }
}
