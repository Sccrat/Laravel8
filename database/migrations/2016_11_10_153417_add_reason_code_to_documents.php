<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReasonCodeToDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wms_documents', function($table) {
            $table->integer('reason_code_id')->unsigned()->nullable();

            $table->foreign('reason_code_id')
            ->references('id')->on('wms_reason_codes')
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
        Schema::table('wms_documents', function($table) {
            $table->dropColumn('reason_code_id');
        });
    }
}
