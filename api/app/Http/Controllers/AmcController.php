<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class AmcController extends Controller
{
    public function index()
    {
    }

    public function fetchAmcData()
    {
        // SELECT title FROM watchlist WHERE watched = 0 AND released = 1
        // UNION
        // (SELECT title FROM watchlist WHERE watched = 1 ORDER BY watched_date DESC LIMIT 6)
        // UNION
        // (SELECT search FROM amc_data)"

        $excludeTitles = [];

        $response = Http::withHeaders([
            "X-AMC-Vendor-Key" => env("AMC_KEY")
        ])->get('https://api.amctheatres.com/v2/movies/views/now-playing', [
            'page-size' => 50
        ]);

        $amcDataArray = $response->json()['_embedded']['movies'];

        $amcMovies = [];
        foreach ($amcDataArray as $amcMovie) {

            $movieTitle = trim($amcMovie['name']);
            //TODO: Need better title comparison
            if ($movieTitle == '$99 Private Theatre Rental' || in_array($movieTitle, $excludeTitles)) {
                continue;
            }

            $amcMovies[] = $movieTitle;
        }

        foreach ($amcMovies as $amcTitle) {
            $movie = new Movie();

            $movieData = $this->searchMovie($amcTitle);

            $movie->title = $movieData['title'] ?? $amcTitle;
            $movie->description = $movieData['description'] ?? null;
            $movie->tomato = $movieData['tomato'] ?? null;
            $movie->imdb = $movieData['imdb'] ?? null;
            $movie->poster_url = $movieData['image'] ?? null;
            $movie->trailer_url = $movieData['trailer'] ?? null;
            $movie->rating = $movieData['rating'] ?? null;
            $movie->year = $movieData['year'] ?? null;
            $movie->genre = $movieData['genre'] ?? null;
            $movie->runtime = $movieData['runtime'] ?? null;
            $movie->release_date = $movieData['releaseDate'] ?? null;
            $movie->amc_title = $amcTitle;

            // 'services' => '',
            // 'releaseDate' => $movie['releaseDate'] ?? '',
            // 'released' => 1,
            // 'add_date' => date("Y-m-d H:i:s"),
            // 'amc' => 1,

            $movie->save();
        }
    }

    private function searchMovie(string $searchTerm)
    {
        $pythonPath = resource_path() . "/python/";
        $process = new Process([$pythonPath . ".env/Scripts/python.exe", $pythonPath . 'movieScraper.py', $searchTerm]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }
}
