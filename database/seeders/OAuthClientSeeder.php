<?php

use Illuminate\Database\Seeder;

class OAuthClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Create an oauth client
        \App\Models\OAuthClient::create([
          'id' => '2898af4342db33daf87432ec',
          'secret' => '9a05800e6a915571e9ead232',
          'name' => 'WMS WebSite'
        ]);

        \App\Models\OAuthClient::create([
          'id' => '1e8682870781aa182882d2e1',
          'secret' => '2a928a91bee7cae5e4d0bbc2',
          'name' => 'Saya'
        ]);
    }
}
