<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeColumnsDocumentIncrements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::table('wms_documents', function ($table) {
        //     $table->integer('weight')->nullable()->change();
        //     $table->integer('volumen')->nullable()->change();
        // });
        DB::statement('ALTER TABLE wms_documents MODIFY weight INT(10) UNSIGNED NULL, MODIFY volumen INT(10) NULL;');
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
