<?php

use Illuminate\Database\Seeder;

class VinculationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $configs = array(
        array('name' => 'Fijo'),
        array('name' => 'Temporal'),
        array('name' => 'Auditoria'),
        array('name' => 'Honorarios'),
        array('name' => 'Servicios'),
      );
        //Delete the configs table
        // DB::table('wms_vinculation_types')->delete();

        //Insert the data
        DB::table('wms_vinculation_types')->insert($configs);
    }
}
