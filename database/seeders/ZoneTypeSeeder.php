<?php

use Illuminate\Database\Seeder;

class ZoneTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $types = array(
          array('name' => 'Almacenamiento'),
          array('name' => 'Puertas'),
          array('name' => 'Oficina'),
          array('name' => 'Baños'),
          array('name' => 'Muelle'),
          array('name' => 'Máquinas'),
          array('name' => 'Cuarentena'),
        );

        //Insert the data
        DB::table('wms_zone_types')->insert($types);

    }
}
