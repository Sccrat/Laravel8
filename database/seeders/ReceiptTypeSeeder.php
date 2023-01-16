<?php

use Illuminate\Database\Seeder;

class ReceiptTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $types = array(
        array('name' => 'Insumos', 'active' => true, 'document_name' => 'Factura Insumos', 'required_partial' => true, 'is_import' => false),
        array('name' => 'Importación', 'active' => true, 'document_name' => 'Orden de compra', 'required_partial' => false, 'is_import' => true),
        array('name' => 'Nacionales', 'active' => true, 'document_name' => 'Factura Nacional', 'required_partial' => true, 'is_import' => false)
      );

      //Insert the data
      DB::table('wms_receipt_types')->insert($types);
    }
}
