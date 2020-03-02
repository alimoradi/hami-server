<?php

use Illuminate\Database\Seeder;

class ProviderCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('provider_categories')->truncate();
        DB::table('provider_categories')->insert([
            [
                'name'=> 'خانواده'
            ],
            [
                'name' => 'ازدواج'
            ],
            [
                'name' => 'کودکان'
            ]
        ]);
    }
}
