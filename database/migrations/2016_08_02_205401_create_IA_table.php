<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIATable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
     {
       Schema::create('wms_ia_codes', function (Blueprint $table) {
           $table->increments('id');
           $table->string('code_ia',10);
           $table->string('name', 100);
           $table->string('table', 100);
           $table->string('field', 100);
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
