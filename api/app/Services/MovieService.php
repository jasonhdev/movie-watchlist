<?php

namespace App\Services;

use App\Models\AmcData;
use App\Models\Movie;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MovieService {

    public function searchMovie(string $searchTerm): ?array {
        try {
            $response = Http::timeout(10)->get('https://www.omdbapi.com/', [
                'apikey' => env('OMDB_API_KEY'),
                't' => $searchTerm,
            ]);

            $response->throw();
            $omdbData = $response->json();

            if (($omdbData['Response'] ?? 'False') !== 'True') {
                return ['title' => $searchTerm];
            }

            $rottenTomatoes = collect($omdbData['Ratings'] ?? [])
                ->firstWhere('Source', 'Rotten Tomatoes')['Value'] ?? null;

            $movieData = [
                'title' => $omdbData['Title'] ?? $searchTerm,
                'description' => $omdbData['Plot'] ?? null,
                'tomato' => $rottenTomatoes,
                'imdb' => $omdbData['imdbRating'] ?? null,
                'image' => ($omdbData['Poster'] ?? 'N/A') !== 'N/A' ? $omdbData['Poster'] : null,
                'trailer' => null,
                'rating' => $omdbData['Rated'] ?? null,
                'year' => $omdbData['Year'] ?? null,
                'genre' => $omdbData['Genre'] ?? null,
                'runtime' => $omdbData['Runtime'] ?? null,
                'services' => null,
                'releaseDate' => $omdbData['Released'] ?? null,
            ];
        } catch (Exception $e) {
            Log::error('Error fetching movie from OMDb: ' . $e->getMessage());
            return ['title' => $searchTerm];
        }

        if (!$movieData || !isset($movieData['title'])) {
            $movieData['title'] = $searchTerm;
        }

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

    public function getRefreshedMovieData(Movie $movie): Movie {
        if ($movieData = $this->searchMovie($movie->search_term ?? $movie->title)) {
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
        if (null !== $releaseDate && !$movie->released) {
            $movie->released = strtotime($releaseDate) < strtotime("today");
        }

        return $movie;
    }
}
