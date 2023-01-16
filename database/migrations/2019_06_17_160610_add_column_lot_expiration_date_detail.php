<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnLotExpirationDateDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::table('wms_document_details', function ($table)
        {
            $table->string('expiration_date')->nullable();
            $table->string('lot')->nullable();
            // $table->string('weight')->nullable();
            $table->string('meters')->nullable();
        });

        Schema::table('wms_products', function ($table)
        {
            $table->integer('window')->nullable();
        });
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
