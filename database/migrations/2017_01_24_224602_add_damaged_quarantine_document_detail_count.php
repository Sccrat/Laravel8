<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDamagedQuarantineDocumentDetailCount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wms_document_detail_count', function (Blueprint $table) {
            $table->boolean('damaged')->default(false);  
            $table->boolean('quarantine')->default(false);  
            $table->integer('count_parent')->unsigned()->nullable();
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
            $table->dropColumn('damaged');
            $table->dropColumn('quarantine');
            $table->dropColumn('count_parent');
        });
    }
}
