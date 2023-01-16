<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCountDocumentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_document_detail_count', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('document_detail_id')->unsigned();
            $table->integer('quanty')->unsigned()->nullable();
            $table->boolean('is_additional')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('wms_document_detail_count');
    }
}
