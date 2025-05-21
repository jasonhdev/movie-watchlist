<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\AmcData;
use App\Services\MovieService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MovieController extends Controller {
    const LIST_WATCH = "watch";
    const LIST_UPCOMING = "upcoming";
    const LIST_HISTORY = "history";

    const ACTION_WATCH = "watch";
    const ACTION_FEATURE = "feature";
    const ACTION_DELETE = "delete";
    const ACTION_REFRESH = "refresh";

    private MovieService $movieService;

    public function __construct(Request $request, MovieService $movieService) {
        parent::__construct($request);

        $this->movieService = $movieService;
    }

    public function index(Request $request): JsonResponse {
        $list = $request->list;

        $movies = [];
        switch ($list) {
            case self::LIST_UPCOMING:
                $movies = Movie::select('*')
                    ->where('watched', '=', 0)
                    ->where('released', '=', 0)
                    ->orderBy('release_date', 'desc')
                    ->get();
                break;
            case self::LIST_HISTORY:
                $movies = Movie::select('*')
                    ->where('watched', '=', 1)
                    ->orderBy('watched_date', 'desc')
                    ->get();

                foreach ($movies as $key => $movie) {
                    $movies[$key]['watched_date'] = date("M j, Y", strtotime($movie['watched_date']));
                }
                break;
            case self::LIST_WATCH:
                $movies = Movie::select('*')
                    ->where('watched', '=', 0)
                    ->where('released', '=', 1)
                    ->orderBy('featured', 'desc')
                    ->orderBy('amc', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->get();

                break;
            default:
                $movies = Movie::all();
                break;
        }

        return response()->json($movies);
    }

    // Unused, for testing by url
    public function testSearchMovie(Request $request): JsonResponse {
        $movieData = [];
        if ($searchTerm = $request->searchTerm) {
            $movieData = $this->movieService->searchMovie($searchTerm);
        }

        return response()->json([
            'success' => true,
            'movieData' => $movieData,
        ]);
    }

    public function createMovie(Request $request): JsonResponse {
        $movie = new Movie();

        $list = $request->list;
        if ($list === self::LIST_UPCOMING) {
            $movie->released = false;
        }

        if ($list === self::LIST_HISTORY) {
            $movie->watched = true;
            $movie->watched_date = date("Y-m-d H:i:s");
        }

        if ($this->loggedIn) {
            $movie->title = $request->searchTerm;
            $movie->save();
        }

        $movieData = $this->movieService->searchMovie($request->searchTerm);

        $movie->title = $movieData['title'] ?? $request->searchTerm;
        $movie->description = $movieData['description'] ?? null;
        $movie->tomato = $movieData['tomato'] ?? null;
        $movie->imdb = $movieData['imdb'] ?? null;
        $movie->poster_url = $movieData['image'] ?? null;
        $movie->trailer_url = $movieData['trailer'] ?? null;
        $movie->rating = $movieData['rating'] ?? null;
        $movie->year = $movieData['year'] ?? null;
        $movie->genre = $movieData['genre'] ?? null;
        $movie->runtime = $movieData['runtime'] ?? null;
        $movie->services = $movieData['services'] ?? null;
        $movie->release_date = $movieData['releaseDate'] ?? null;
        $movie->amc = $movieData['amc'] ?? 0;

        $movie->search_term = $request->searchTerm;

        if ($this->loggedIn) {
            $movie->save();
        }

        return response()->json([
            'message' => 'Movie added.',
            'movie' => $movie,
        ]);
    }

    public function updateMovie(Request $request, $id): JsonResponse {
        if (Movie::where('id', $id)->exists()) {
            $movie = Movie::find($id);

            switch ($action = $request->get('action')) {
                case self::ACTION_WATCH:
                    $movie->watched = $request->watched;
                    $movie->watched_date = date("Y-m-d H:i:s");
                    $movie->released = $request->released;
                    break;

                case self::ACTION_FEATURE:
                    $movie->featured = $request->featured;
                    break;

                case self::ACTION_REFRESH:
                    $movie = $this->movieService->getRefreshedMovieData($movie);
                    break;

                default:
                    break;
            }

            if ($this->loggedIn) {
                $movie->save();
            }

            // Display clean data after save
            $movie->watched_date = date("M j, Y", strtotime($movie->watched_date));

            return response()->json([
                'message' => 'Movie updated.',
                'action' => $action,
                'movie' => $movie,
            ]);
        } else {
            return response()->json([
                'message' => 'Movie not found.'
            ]);
        }
    }

    public function deleteMovie(int $id): JsonResponse {
        if (Movie::where('id', $id)->exists()) {
            $movie = Movie::find($id);

            if ($this->loggedIn) {
                $movie->delete();
            }

            return response()->json([
                'message' => 'Movie deleted.',
                'action' => self::ACTION_DELETE,
                'movie' => $movie,
            ]);
        } else {
            return response()->json([
                'message' => 'Movie not found.'
            ]);
        }
    }

    public function refreshBatch(): void {
        $movies = Movie::select('*')
            ->where(function ($query) {
                $query->where('watched', '=', 0)
                    ->orWhere('released', '=', 0);
            })
            ->where(function ($query) {
                $query->whereNull('year')
                    ->orWhere('year', '>=', date("Y"));
            })
            ->orderBy('updated_at', 'asc')
            ->limit(10)
            ->get();

        foreach ($movies as $movie) {
            $movie = $this->movieService->getRefreshedMovieData($movie);
            $movie->save();
        }
    }
}
