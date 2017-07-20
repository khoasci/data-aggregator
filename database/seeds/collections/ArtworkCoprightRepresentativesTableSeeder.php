<?php

use Illuminate\Database\Seeder;

class ArtworkCopyrightRepresentativesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $artworks = App\Models\Collections\Artwork::all()->all();

        foreach ($artworks as $artwork) {

            $artwork->seedCopyrightRepresentatives();

        }

    }

}
