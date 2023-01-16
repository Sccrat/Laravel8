<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentDetailMultiplesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_document_detail_multiples', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('document_detail_id')->unsigned();
            $table->integer('product_id')->unsigned();
            $table->integer('quanty')->unsigned()->nullable();


            $table->foreign('product_id')
                ->references('id')->on('wms_products')
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
        Schema::drop('wms_document_detail_multiples');
    }
}
