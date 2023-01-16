<?php

use Illuminate\Database\Seeder;

class SchemaSeeder extends Seeder
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
        array('name' => 'Línea'),
        array('name' => 'Moda'),
        array('name' => 'Temporada'),
        array('name' => 'Saldos')
      );
        //Delete the configs table
        DB::table('wms_schemas')->delete();

        //Insert the data
        DB::table('wms_schemas')->insert($configs);
    }
}
