<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NewDocumentFieldsLogisticc extends Migration
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
            $table->string('provider')->nullable();
            $table->string('agent')->nullable();
            $table->string('agent_phone')->nullable();
            $table->string('driver_name')->nullable();
            $table->string('driver_identification')->nullable();
            $table->string('driver_phone')->nullable();
            $table->string('transport_company')->nullable();
            $table->string('transport_company_phone')->nullable();
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
