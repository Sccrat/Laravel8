<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReceiptType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('wms_documents', function (Blueprint $table) {
          $table->integer('receipt_type_id')->unsigned()->nullable();

          $table->foreign('receipt_type_id')->references('id')->on('wms_receipt_types')->onDelete('cascade');
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
