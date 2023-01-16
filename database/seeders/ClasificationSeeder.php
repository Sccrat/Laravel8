<?php

use Illuminate\Database\Seeder;

class ClasificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $types = array(
        array('name' => 'Interno' , 'active' => true),
        array('name' => 'Externo', 'active' => true),
      );

      //Insert the data
      DB::table('wms_container_clasifications')->insert($types);
    }
}
