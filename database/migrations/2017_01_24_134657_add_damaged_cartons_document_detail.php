<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDamagedCartonsDocumentDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wms_document_details', function (Blueprint $table) {
            $table->integer('damaged_cartons')->unsigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wms_document_details', function (Blueprint $table) {
            $table->dropColumn('damaged_cartons');
        });
    }
}
