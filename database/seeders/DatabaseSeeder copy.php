<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        
                //$this->call(TestSeedTable::class);
         $this->call(UserSeedTable::class);
        $this->call(OAuthClientSeeder::class);
        // $this->call(AreaSeeder::class);
        // $this->call(StructureTypeSeeder::class);
        $this->call('Alakkad\WorldCountriesCities\CountriesSeeder');
        $this->call('Alakkad\WorldCountriesCities\CitiesSeeder');
        $this->call(SettingsSeeder::class);
        // $this->call(SpecialDocumentTableSeeder::class);
        Model::reguard();
    }
}
