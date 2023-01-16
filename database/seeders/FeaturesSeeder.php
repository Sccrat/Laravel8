<?php

use Illuminate\Database\Seeder;

class FeaturesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $features = array(
        array('name' => 'Alto (mts)'),
        array('name' => 'Ancho (mts)'),
        array('name' => 'Profundidad (mts)'),
        array('name' => 'Capacidad (kg)')
      );
        //Delete the features table
        DB::table('wms_features')->delete();

        //Insert the data
        DB::table('wms_features')->insert($features);
    }
}
