<?php

namespace App\Services;

use App\Models\AmcData;
use App\Models\Movie;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use App\Services\DB;

class MovieService
{
    public function searchMovie(string $searchTerm): ?array
    {
        $process = new Process([env("PYTHON_PATH"), resource_path() . "/python/movieScraper.py", $searchTerm]);

        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $movieData = json_decode($process->getOutput(), true);

        // Check if movie is playing at AMC
        $titleCount = 0;
        if ($movieData) {
            $titleCount = AmcData::select('*')
                ->where('title', 'LIKE', "%$searchTerm%")
                ->orWhere('title', 'LIKE', "%" . $movieData['title'] . "%")
                ->count();
        }

        $movieData['amc'] = $titleCount >= 1;

        return $movieData;
    }

    public function getRefreshedMovieData(Movie $movie): Movie
    {
        if ($movieData = $this->searchMovie($movie->search_term)) {
            $movie->title = $movieData['title'] ?? $movie->title;
            $movie->description = $movieData['description'] ?? $movie->description;
            $movie->tomato = $movieData['tomato'] ?? $movie->tomato;
            $movie->imdb = $movieData['imdb'] ?? $movie->imdb;
            $movie->poster_url = $movieData['image'] ??  $movie->poster_url;
            $movie->trailer_url = $movieData['trailer'] ?? $movie->trailer_url;
            $movie->rating = $movieData['rating'] ?? $movie->rating;
            $movie->year = $movieData['year'] ?? $movie->year;
            $movie->genre = $movieData['genre'] ?? $movie->genre;
            $movie->runtime = $movieData['runtime'] ?? $movie->runtime;
            $movie->services = $movieData['services'] ?? $movie->services;
            $movie->release_date = $movieData['releaseDate'] ?? $movie->release_date;
            $movie->amc = $movieData['amc'] ?? 0;
        }

        $releaseDate = $movie->release_date;
        if ($releaseDate && !$movie->released) {
            $movie->released = strtotime($releaseDate) < strtotime("today");
        }

        return $movie;
    }
}
