<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEanCodes128Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('wms_ean_codes128', function (Blueprint $table) {
          $table->increments('id');
          $table->integer('document_id')->unsigned();
          $table->string('code128',500);
          $table->boolean('canceled')->default(false);
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
