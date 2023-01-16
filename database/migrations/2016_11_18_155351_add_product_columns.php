<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProductColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wms_products', function ($table)
        {
          $table->string('ean', 200)->nullable();
          $table->string('serial', 200)->nullable();
          $table->string('alt_code', 50)->nullable();
          $table->string('complement_size', 10)->nullable();

          $table->integer('client_id')->unsigned()->nullable();
          $table->integer('brand_id')->unsigned()->nullable();
          $table->integer('schema_id')->unsigned()->nullable();

          $table->foreign('brand_id')->references('id')->on('wms_brands');
          $table->foreign('schema_id')->references('id')->on('wms_schemas');
          $table->foreign('client_id')->references('id')->on('wms_clients');

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
