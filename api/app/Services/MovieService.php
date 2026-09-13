<?php

namespace App\Services;

use App\Models\AmcData;
use App\Models\Movie;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MovieService {

public function searchMovie(string $searchTerm): ?array
{
    try {
        $apiKey = env('OMDB_API_KEY');

        // First try the full search term
        $response = Http::timeout(10)->get('https://www.omdbapi.com/', [
            'apikey' => $apiKey,
            's' => $searchTerm,
            'type' => 'movie',
        ]);

        $response->throw();
        $omdbData = $response->json();

        // If no results, try progressively shorter phrases
        if (($omdbData['Response'] ?? 'False') !== 'True') {
            $words = preg_split('/\s+/', trim($searchTerm));

            while (count($words) > 1) {
                array_shift($words);

                $fallbackTerm = implode(' ', $words);

                $response = Http::timeout(10)->get('https://www.omdbapi.com/', [
                    'apikey' => $apiKey,
                    's' => $fallbackTerm,
                    'type' => 'movie',
                ]);

                $response->throw();
                $omdbData = $response->json();

                if (($omdbData['Response'] ?? 'False') === 'True') {
                    break;
                }
            }
        }

        if (($omdbData['Response'] ?? 'False') !== 'True') {
            return ['title' => $searchTerm];
        }

        // Pick the first result
        $result = $omdbData['Search'][0] ?? null;

        if (!$result) {
            return ['title' => $searchTerm];
        }

        // Now get the full movie details
        $detailResponse = Http::timeout(10)->get('https://www.omdbapi.com/', [
            'apikey' => $apiKey,
            'i' => $result['imdbID'],
        ]);

        $detailResponse->throw();
        $omdbData = $detailResponse->json();

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
            'image' => ($omdbData['Poster'] ?? 'N/A') !== 'N/A'
                ? $omdbData['Poster']
                : null,
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

    // Check if movie is playing at AMC
    $titleCount = AmcData::where('title', 'LIKE', "%{$searchTerm}%")
        ->orWhere('title', 'LIKE', "%{$movieData['title']}%")
        ->count();

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
