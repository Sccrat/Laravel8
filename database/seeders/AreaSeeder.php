<?php

use Illuminate\Database\Seeder;

class AreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      \App\Models\Area::create([
        'name' => 'Puertas'
      ]);

      \App\Models\Area::create([
        'name' => 'Muelles'
      ]);

      \App\Models\Area::create([
        'name' => 'Alistamiento'
      ]);

      \App\Models\Area::create([
        'name' => 'Máquinas'
      ]);

      \App\Models\Area::create([
        'name' => 'Oficina'
      ]);

      \App\Models\Area::create([
        'name' => 'Almacenamiento',
        'is_storage' => true
      ]);
    }
}
