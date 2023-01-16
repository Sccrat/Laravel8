<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQuanty123DocumentDetailCountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wms_document_detail_count', function (Blueprint $table) {
            $table->integer('quanty1')->unsigned()->nullable();
            $table->integer('quanty2')->unsigned()->nullable();
            $table->integer('quanty3')->unsigned()->nullable();
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
            $table->dropColumn('quanty1');
            $table->dropColumn('quanty2');
            $table->dropColumn('quanty3');
        });
    }
}
