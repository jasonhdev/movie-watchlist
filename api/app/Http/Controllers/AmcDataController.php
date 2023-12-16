<?php

namespace App\Http\Controllers;

use App\Models\AmcData;
use App\Models\Movie;
use App\Services\MovieService;
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

    private MovieService $movieService;

    public function __construct(MovieService $movieService)
    {
        $this->movieService = $movieService;
    }

    public function index(): JsonResponse
    {
        $movies = AmcData::select('*')
            ->where('added', '!=', 1)
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

    public function fetchAmcData(): void
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
        $alreadyAdded = $watchlistQuery->union($historyQuery)
            ->get()
            ->pluck('title')
            ->toArray();

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

            if (in_array($title, self::IGNORED_WORDS)) {
                continue;
            }

            if (in_array($title, $alreadyAdded)) {
                // Add skeleton of movies already in watchlist for usage on refresh action
                $movie = new AmcData();
                $movie->title = $title;
                $movie->added = true;
                $movie->amc_title = $title;
                $movie->save();
                continue;
            }

            $amcMovies[] = $title;
        }

        foreach ($amcMovies as $amcTitle) {
            $movie = new AmcData();

            $movieData = $this->movieService->searchMovie($amcTitle);

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

            $movie->save();
        }
    }

    public function createMovieFromData(int $id): JsonResponse
    {
        $movie = new Movie();

        if (AmcData::where('id', $id)->exists()) {
            $amcData = AmcData::find($id);
            $amcData->added = 1;
            $amcData->save();

            $amcDataArray = $amcData->toArray();
            $amcDataArray['search_term'] = $amcDataArray['amc_title'];
            $amcDataArray['released'] = 1;
            $amcDataArray['amc'] = 1;

            unset($amcDataArray['amc_title'], $amcDataArray['added']);

            $movie->fill($amcDataArray);
            $movie->save();

            return response()->json([
                'message' => 'AMC movie added.',
                'movie' => $movie,
            ]);
        }
    }
}
