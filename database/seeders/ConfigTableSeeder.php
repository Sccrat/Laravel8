<?php

use Illuminate\Database\Seeder;

class ConfigTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      //Set the data
      $configs = array(
        array('key' => 'dc_size', 'value' => '3', 'description' => 'Tamaño códigos centro distribución'),
        array('key' => 'warehouse_size', 'value' => '3', 'description' => 'Tamaño códigos bodegas'),
        array('key' => 'receipt_charge', 'value' => 'Etiqueteo y empaque', 'description' => 'Cargo de los empleados para recibir contenedores'),
      );
        //Delete the configs table
        DB::table('wms_settings')->delete();

        //Insert the data
        DB::table('wms_settings')->insert($configs);
    }
}
