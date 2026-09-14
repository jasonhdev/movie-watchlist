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
            $searchTerm = trim($searchTerm);
            if ($searchTerm === '') {
                return null;
            }
            $response = Http::timeout(10)->get('https://www.omdbapi.com/', ['apikey' => env('OMDB_API_KEY'), 't' => $searchTerm]);
            $response->throw();
            $omdbData = $response->json();
            if (($omdbData['Response'] ?? 'False') === 'True') {
                $requestedTitle = $this->normalizeMovieTitle($searchTerm);
                $returnedTitle = $this->normalizeMovieTitle($omdbData['Title'] ?? '');
                $matchScore = $this->movieTitleMatchScore($requestedTitle, $returnedTitle);
                if ($matchScore >= 70) {
                    $omdbData['match_score'] = $matchScore;
                    return $omdbData;
                }
            }
            $searchResponse = Http::timeout(10)->get('https://www.omdbapi.com/', ['apikey' => env('OMDB_API_KEY'), 's' => $searchTerm]);
            $searchResponse->throw();
            $searchData = $searchResponse->json();
            if (($searchData['Response'] ?? 'False') !== 'True') {
                return ['Title' => $searchTerm, 'title' => $searchTerm,];
            }
            $results = $searchData['Search'] ?? [];
            if (empty($results)) {
                return ['Title' => $searchTerm, 'title' => $searchTerm,];
            }
            $bestMatch = null;
            $bestScore = 0;
            foreach ($results as $result) {
                if (empty($result['Title'])) {
                    continue;
                }
                $candidateTitle = $this->normalizeMovieTitle($result['Title']);
                $score = $this->movieTitleMatchScore($this->normalizeMovieTitle($searchTerm), $candidateTitle);
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestMatch = $result;
                }
            }
            if (!$bestMatch || $bestScore < 90) {
                return ['Title' => $searchTerm, 'title' => $searchTerm,];
            }
            if (!empty($bestMatch['imdbID'])) {
                $detailResponse = Http::timeout(10)->get('https://www.omdbapi.com/', ['apikey' => env('OMDB_API_KEY'), 'i' => $bestMatch['imdbID']]);
                $detailResponse->throw();
                $detailData = $detailResponse->json();
                if (($detailData['Response'] ?? 'False') === 'True') {
                    $detailData['match_score'] = $bestScore;
                    return $detailData;
                }
            }
            $bestMatch['match_score'] = $bestScore;
            return $bestMatch;
        } catch (Exception $e) {
            Log::error('Error searching OMDb: ' . $e->getMessage(), ['search_term' => $searchTerm,]);
            return ['Title' => $searchTerm, 'title' => $searchTerm,];
        }
    }
    private function normalizeMovieTitle(string $title): string {
        $title = mb_strtolower(trim($title));
        $title = preg_replace('/[\(\[\{]\s*\d{4}\s*[\)\]\}]/', '', $title);
        $title = preg_replace('/\s+\d{4}$/', '', $title);
        $title = str_replace(['&', ':', '-', '–', '—', '\'', '"', '.', ',', '!', '?',], [' and ', ' ', ' ', ' ', ' ', '', '', '', '', '', '',], $title);
        $title = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $title);
        $title = preg_replace('/\s+/', ' ', $title);
        return trim($title);
    }
    private function movieTitleMatchScore(string $searchTitle, string $candidateTitle): int {
        if ($searchTitle === '' || $candidateTitle === '') {
            return 0;
        }
        if ($searchTitle === $candidateTitle) {
            return 100;
        }
        $searchWords = preg_split('/\s+/', $searchTitle);
        $candidateWords = preg_split('/\s+/', $candidateTitle);
        $searchWordCount = count($searchWords);
        $candidateWordCount = count($candidateWords);
        if ($searchWordCount === 0 || $candidateWordCount === 0) {
            return 0;
        }
        $searchPhrase = implode(' ', $searchWords);
        $candidatePhrase = implode(' ', $candidateWords);
        if (str_starts_with($candidatePhrase, $searchPhrase)) {
            $extraWords = $candidateWordCount - $searchWordCount;
            if ($extraWords === 1) {
                return 90;
            }
            if ($extraWords === 2) {
                return 80;
            }
            return 70;
        }
        if (str_contains($candidatePhrase, $searchPhrase)) {
            return 40;
        }
        $matchingWords = count(array_intersect($searchWords, $candidateWords));
        $wordMatchRatio = $matchingWords / $searchWordCount;
        if ($wordMatchRatio < 1.0) {
            return 0;
        }
        $extraWords = $candidateWordCount - $searchWordCount;
        if ($extraWords === 0) {
            return 100;
        }
        if ($extraWords === 1) {
            return 85;
        }
        return 75;
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
