<?php

namespace App\Http\Controllers;

use App\Models\AmcData;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Http\JsonResponse;

class AmcDataController extends Controller
{
    const IGNORED_WORDS = [
        '$99 Private Theatre Rental',
    ];

    public function index(): JsonResponse
    {
        $movies = AmcData::select('*')
            ->orderBy('tomato', 'desc')
            ->orderBy('imdb', 'desc')
            ->get()
            ->toArray();

        // Scoring system: Rotten tomato gets 1.5 multiplier
        foreach ($movies as $key => &$movie) {
            $tomato = (float) str_replace("%", "", $movie['tomato']);
            $tomato *= 1.5;

            $imdb = strstr($movie['imdb'], "/", true) ?: 0;
            $imdb = (float) str_replace(".", "", $imdb);

            $diviser = (($tomato > 0) + ($imdb > 0)) ?: -1;

            $movie['score'] = ($tomato + $imdb) / $diviser;
        }

        usort($movies, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return response()->json($movies);
    }

    public function fetchAmcData()
    {
        // Purge table to always have fresh data
        AmcData::query()->delete();

        $watchlistQuery = Movie::select('title')
            ->where('watched', '=', 0)
            ->where('released', '=', 1);

        $historyQuery = Movie::select('title')
            ->where('watched', '=', 1)
            ->orderBy('watched_date', 'desc')
            ->limit(6);

        // Skip fetch on already added movies and any bad amc API data
        $excludeTitles = $watchlistQuery->union($historyQuery)
            ->get()
            ->pluck('title')
            ->toArray();

        $excludeTitles = array_merge($excludeTitles, self::IGNORED_WORDS);

        $response = Http::withHeaders([
            "X-AMC-Vendor-Key" => env("AMC_KEY")
        ])->get('https://api.amctheatres.com/v2/movies/views/now-playing', [
            'page-size' => 50
        ]);

        $amcDataArray = $response->json()['_embedded']['movies'];

        $amcMovies = [];
        foreach ($amcDataArray as $amcData) {

            $title = trim($amcData['name']);
            //TODO: Need better title comparison
            if (in_array($title, $excludeTitles)) {
                continue;
            }

            $amcMovies[] = $title;
        }

        foreach ($amcMovies as $amcTitle) {
            $movie = new AmcData();

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

        return json_decode($process->getOutput(), true);
    }
}
