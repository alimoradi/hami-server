<?php

use Illuminate\Database\Seeder;

class ProvidersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('providers')->truncate();
        DB::table('providers')->insert([
            [
                'user_id'=> '3',
                'provider_category_id' => '1'
            ]
        ]);    }
}
