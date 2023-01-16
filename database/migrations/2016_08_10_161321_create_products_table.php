<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
     {
         Schema::create('wms_products', function (Blueprint $table) {
             $table->increments('id');
             $table->string('name',50);
             $table->string('description',200);
             $table->integer('product_type_id')->unsigned();
             $table->timestamps();
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
        //
    }
}
