<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductSubTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_product_sub_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50);
            $table->string('code', 5);
            $table->boolean('active')->default(true);
            $table->integer('product_type_id')->unsigned();

            $table->foreign('product_type_id')
                  ->references('id')->on('wms_product_types')
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
        Schema::drop('wms_product_sub_types');
    }
}
