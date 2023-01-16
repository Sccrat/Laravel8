<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTableDocumentDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wms_document_details', function ($table) {

            $table->integer('product_id')->unsigned()->nullable();
            $table->foreign('product_id')
                  ->references('id')->on('wms_products')
                  ->onDelete('cascade');

            $table->dropColumn('reference');
            $table->dropColumn('description');
            $table->dropColumn('size');
            $table->dropColumn('colour');
            $table->dropColumn('code');
            $table->dropColumn('ean');
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
