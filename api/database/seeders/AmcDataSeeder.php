<?php

namespace Database\Seeders;

use App\Models\AmcData;
use Illuminate\Database\Seeder;
use File;

class AmcDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = File::get("database/data/amcData.json");
        $amcData = json_decode($json);
  
        foreach ($amcData as $value) {
            AmcData::create([
                "id" => $value->id,
                "title" => $value->title,
                "description" => $value->description,
                "tomato" => $value->tomato,
                "imdb" => $value->imdb,
                "poster_url" => $value->poster_url,
                "trailer_url" => $value->trailer_url,
                "rating" => $value->rating,
                "year" => $value->year,
                "genre" => $value->genre,
                "runtime" => $value->runtime,
                "release_date" => $value->release_date,
                "amc_title" => $value->amc_title,
                "created_at" => $value->created_at,
                "updated_at" => $value->updated_at,
                "added" => $value->added,
            ]);
        }
    }
}
