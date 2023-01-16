<?php

use Illuminate\Database\Seeder;

class StructureTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        \App\Models\StructureType::create([
          'name' => 'Centro distribución',
          'company_id' => 1,
          'parent_required' => false
        ]);

        \App\Models\StructureType::create([
          'name' => 'Almacén',
          'company_id' => 1,
          'configurable' => true
        ]);

        \App\Models\StructureType::create([
          'name' => 'Zona',
          'company_id' => 1
        ]);

        \App\Models\StructureType::create([
          'name' => 'Bodega',
          'company_id' => 1,
          'configurable' => true
        ]);

        //Almacen , zonas , bodega
    }
}
