<?php

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\User;

class UserSeedTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $company = new Company();
        $company->name = 'CIIBLUE';
        $company->save();

        $user = new User();
        $user->name = 'Administrator';
        $user->username = 'admin';
        $user->email = 'admin@admin.com';
        $user->password = bcrypt('123456');
        $user->remember_token = str_random(10);

        $company->users()->save($user);
    }
}
