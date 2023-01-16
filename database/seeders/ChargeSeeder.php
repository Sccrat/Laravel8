<?php

use Illuminate\Database\Seeder;

class ChargeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $charges = array(
          array('name' => 'Etiqueteo y empaque'),
          array('name' => 'Operador de máquinas'),
          array('name' => 'Auxiliares de showroom'),
          array('name' => 'Auxiliares de RR'),
          array('name' => 'Archivos y fotos'),
          array('name' => 'Auxiliar de bodega'),
          array('name' => 'Montacarguista'),
          array('name' => 'Coordinador de bodega'),
          array('name' => 'Oficios generales'),
          array('name' => 'Mantenimiento'),
          array('name' => 'Conductor'),
          array('name' => 'Practicante administrativo'),
          array('name' => 'Logística'),
          array('name' => 'Administrativo')
        );

        //Delete the configs table
        // DB::table('wms_charges')->delete();

        //Insert the data
        DB::table('wms_charges')->insert($charges);
    }
}
