<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Movie;
use File;

class MovieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = File::get("database/data/movies.json");
        $movies = json_decode($json);
  
        foreach ($movies as $value) {
            Movie::create([
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
                "services" => $value->services,
                "watched" => $value->watched,
                "released" => $value->released,
                "featured" => $value->featured,
                "amc" => $value->amc,
                "watched_date" => $value->watched_date,
                "release_date" => $value->release_date,
                "search_term" => $value->search_term,
                "created_at" => $value->created_at,
                "updated_at" => $value->updated_at,
            ]);
        }
    }
}
