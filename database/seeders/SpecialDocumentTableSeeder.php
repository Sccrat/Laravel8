<?php

use Illuminate\Database\Seeder;

class SpecialDocumentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       $specialDoc = array(
        array(
        	'id' => '0',
        	'active'=>'0',
        	'is_special'=>'1'
        	),
        
      );

        //Insert the data
        DB::table('wms_documents')->insert($specialDoc);
    }
}
