<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_document_details', function (Blueprint $table) {
          $table->increments('id');
          $table->integer('document_id')->unsigned()->nullable();
          $table->string('number', 50)->nullable();
          $table->string('reference', 50)->nullable(); //referencia / Solicitud
          $table->string('description', 200)->nullable(); //Descripción /Producto
          $table->string('size', 10)->nullable(); //Talla
          $table->string('colour', 50)->nullable(); //Color
          $table->string('code', 50)->nullable(); //Código
          $table->string('unit', 50)->nullable(); //Unidad
          $table->string('plu', 50)->nullable(); //Plu éxito
          $table->string('pvp', 50)->nullable(); //Detalle del éxito
          $table->string('ean', 50)->nullable(); //Detalle del éxito          
          $table->double('quanty', 15, 6)->nullable(); //Cantidad unidaddes
          $table->string('package', 50)->nullable(); //Empaque
          $table->double('value', 15, 6)->nullable(); //Valor unitario
          $table->double('iva', 15, 6)->nullable(); //Valor unitario
          $table->double('discount', 15, 6)->nullable(); //Valor unitario
          $table->double('ret_fuente', 15, 6)->nullable(); //Retención en la fiuenta
          $table->double('ret_iva', 15, 6)->nullable(); //Retencion iva
          $table->double('total', 15, 6)->nullable(); //Valor total
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('wms_document_details');
    }
}
