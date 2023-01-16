<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentDetailMultiplesCount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_document_detail_multiples_count', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('document_detail_count_id')->unsigned();
            $table->integer('product_id')->unsigned();
            $table->integer('quanty')->unsigned()->nullable();
            $table->integer('quanty1')->unsigned()->nullable();
            $table->integer('quanty2')->unsigned()->nullable();
            $table->integer('quanty3')->unsigned()->nullable();
            
            $table->foreign('product_id')
                ->references('id')->on('wms_products')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('wms_document_detail_multiples_count');
    }
}
