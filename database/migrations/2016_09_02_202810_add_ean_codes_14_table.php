<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEanCodes14Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('wms_ean_codes14', function (Blueprint $table) {
          $table->increments('id');
          $table->integer('document_detail_id')->unsigned();
          $table->string('code14',500);
          $table->string('code13',500);
          $table->boolean('canceled')->default(false);
          $table->integer('quanty')->unsigned()->nullable();
          $table->timestamps();
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
