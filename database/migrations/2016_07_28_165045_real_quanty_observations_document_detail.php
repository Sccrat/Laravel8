<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RealQuantyObservationsDocumentDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('wms_document_details', function(Blueprint $table)
      {
        $table->double('quanty_received', 15, 6)->nullabe();
        $table->longText('observations')->nullable();// Observaciones
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
