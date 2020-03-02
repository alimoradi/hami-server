<?php

use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->truncate();
        DB::table('roles')->insert([
            [
                'name'=> 'service_provider'
            ],
            [
                'name' => 'service_user'
            ],
            [
                'name' => 'admin'
            ]
        ]);
    }
}
