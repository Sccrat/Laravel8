<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_documents', function (Blueprint $table) {
          $table->increments('id');
          $table->string('number', 50)->nullable(); //Numero de orden o Factura
          $table->string('order_number', 50)->nullable(); //Número de pedido
          $table->string('order_internal', 50)->nullable(); //Número de pedido interno
          $table->string('remision', 50)->nullable(); //Remision
          $table->string('agent')->nullable(); //Agente
          $table->date('date')->nullable(); //Fecha de elaboración
          $table->date('start_date')->nullable(); //Fecha inicio / fecha factura
          $table->date('final_date')->nullable(); //Fecha final / fecha vencimiento
          $table->string('code', 50)->nullable(); //Código
          $table->string('identification', 50)->nullable(); //Nit o identificación
          $table->string('bill_number', 50)->nullable(); //Factura
          $table->integer('total_boxes')->nullable(); //Total de cajas
          $table->string('list', 50)->nullable(); //Lista de empaque
          $table->string('phone_number', 50)->nullable();
          $table->string('city', 50)->nullable();
          $table->string('zone', 50)->nullable(); //Zona / departamento / estado
          $table->string('client', 50)->nullable(); //Tercero cliente / proveedor
          $table->string('address', 50)->nullable(); //direccion /direccion de facturacion
          $table->string('seller', 50)->nullable(); //vendedor
          $table->string('pay_method', 50)->nullable(); //Forma de pago / Condicion de pago
          $table->string('sell_type', 50)->nullable(); //Tipo de venta (credito, contado, etc...)
          $table->string('document', 50)->nullable(); //Documento referencia
          $table->string('delivery_site', 50)->nullable(); //Sitio de entrega
          $table->string('delivery_address', 50)->nullable(); //Dirección de entrega
          $table->string('assistant_code', 50)->nullable(); //Codigo de asesor
          $table->string('delivery_time', 50)->nullable(); //Horario de entrega
          $table->string('client_name', 50)->nullable(); //Nombre cliente almacen
          $table->string('status', 10)->nullable(); //Estado de la orden
          $table->double('quanty', 15, 6)->nullable(); //Cantidad de items
          $table->double('sub_total', 15, 6)->nullable(); //Sub - Total $$
          $table->double('iva', 15, 6)->nullable(); //Iva
          $table->double('ret_fuente', 15, 6)->nullable(); //Retención en la fiuenta
          $table->double('ret_iva', 15, 6)->nullable(); //Retencion iva
          $table->double('total', 15, 6)->nullable(); //Total $$
          $table->double('trm', 15, 6)->nullable(); //Tasa representativa del mercado
          $table->double('weight', 15, 6)->nullable(); //Peso
          $table->double('discount', 15, 6)->nullable(); //Descuento
          $table->longText('observations')->nullable();// Observaciones
          $table->longText('url_document')->nullable();// Url del documento
          $table->boolean('active')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('wms_documents');
    }
}
