<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEan14IdWmsDocumentDetailCount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wms_document_detail_count', function (Blueprint $table) {
            $table->integer('ean14_id')->unsigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wms_document_detail_count', function (Blueprint $table) {
            $table->dropColumn('ean14_id')->unsigned()->nullable();
        });
    }
}
