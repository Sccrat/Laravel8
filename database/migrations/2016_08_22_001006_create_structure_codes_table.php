<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStructureCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('wms_structure_codes', function (Blueprint $table) {
          $table->increments('id');

          $table->integer('ia_code_id')->unsigned();
          $table->foreign('ia_code_id')
                ->references('id')->on('wms_ia_codes')
                ->onDelete('cascade');

          $table->integer('packing_type_id')->unsigned();
          $table->foreign('packing_type_id')
                ->references('id')->on('wms_packing_types')
                ->onDelete('cascade');
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
