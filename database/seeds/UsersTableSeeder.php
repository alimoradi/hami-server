<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->truncate();
        DB::table('users')->insert([
            [
                'first_name' => 'Ali',
                'last_name' => 'Moradi',
                'password' => 'ali123',
                'phone' => '09356269862',
                'role_id' => 1
            ],
            [
                'first_name' => 'Negar',
                'last_name' => 'Asgharnejad',
                'password' => 'negar124',
                'phone' => '09375107255',
                'role_id' => 2
            ],
            [
                'first_name' => 'Jamal',
                'last_name' => 'Taghavi',
                'password' => 'jamal123',
                'phone' => '09124939302',
                'role_id' => 3
            ]

        ]);
    }
}
