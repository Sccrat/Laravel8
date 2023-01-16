<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeColumnPartial extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::table('wms_documents', function ($table) {
        // $table->boolean('is_partial')->nullable()->change();
        // });

        DB::statement("ALTER TABLE wms_documents MODIFY COLUMN  is_partial boolean NULL");
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
