<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnMasterDocument extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wms_documents', function ($table)
        {
            $table->integer('master')->default(0);
        });
        DB::statement('ALTER TABLE wms_documents MODIFY weight decimal(10,2), MODIFY volumen decimal(10,2);');
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
