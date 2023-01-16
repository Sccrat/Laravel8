<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_batches', function (Blueprint $table)
        {
            $table->increments('id');
            $table->integer('document_id')->nullable()->unsigned();
            $table->string('code');
            $table->string('batch_number');
            $table->date('production_date');
            $table->date('expiration_date');


            $table->foreign('document_id')
                  ->references('id')->on('wms_documents')
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
        //
    }
}
