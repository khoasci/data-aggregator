<?php

use Illuminate\Database\Seeder;

class SoundsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Collections\Sound::class, 100)->create();
    }
}