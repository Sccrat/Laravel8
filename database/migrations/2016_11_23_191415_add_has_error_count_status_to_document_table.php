<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHasErrorCountStatusToDocumentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wms_documents', function($table) {
            $table->integer('has_error')->unsigned()->nullable();
            $table->integer('count_status')->unsigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wms_documents', function($table) {
            $table->dropColumn('has_error');
            $table->dropColumn('count_status');
        });
    }
}
