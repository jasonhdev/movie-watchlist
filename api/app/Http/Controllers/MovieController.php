<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Movie;
use Illuminate\Http\JsonResponse;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class MovieController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $list = $request->list;

        $movies = [];
        switch ($list) {
            case "upcoming":
                $movies = Movie::select('*')
                    ->where('watched', '=', 0)
                    ->where('released', '=', 0)
                    ->orderBy('release_date', 'desc')
                    ->get();
                break;
            case "history":
                $movies = Movie::select('*')
                    ->where('watched', '=', 1)
                    ->orderBy('watched_date', 'desc')
                    ->get();

                foreach ($movies as $key => $movie) {
                    $movies[$key]['watched_date'] = date("M j, Y", strtotime($movie['watched_date']));
                }

                break;
            case "watch":
                $movies = Movie::select('*')
                    ->where('watched', '=', 0)
                    ->where('released', '=', 1)
                    ->orderBy('featured', 'desc')
                    ->orderBy('amc', 'desc')
                    ->orderBy('add_date', 'desc')
                    ->get();
                break;
            case "amc":
                // TODO: case "amc":
                break;
            default:
                $movies = Movie::all();
                break;
        }

        return response()->json($movies);
    }

    public function searchMovie(Request $request): JsonResponse
    {
        $searchTerm = $request->searchTerm;

        $pythonPath = resource_path() . "/python/";
        $process = new Process([$pythonPath . ".env/Scripts/python.exe", $pythonPath . 'movieScraper.py', $searchTerm]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return response()->json([
            'success' => true,
            'movieData' => $process->getOutput(),
        ]);
    }

    public function createMovie(Request $request): JsonResponse
    {
        $movie = new Movie();

        $list = $request->list;
        $currentDate = date("Y-m-d H:i:s");

        if ($searchResponse = $this->searchMovie($request)) {

            $movieData = json_decode(json_decode($searchResponse->getContent(), true)['movieData'], true);

            $movie->title = $movieData['title'];
            $movie->description = $movieData['description'];
            $movie->tomato = $movieData['tomato'];
            $movie->imdb = $movieData['imdb'];
            $movie->poster_url = $movieData['image'];
            $movie->trailer_url = $movieData['trailer'];
            $movie->rating = $movieData['rating'];
            $movie->year = $movieData['year'];
            $movie->genre = $movieData['genre'];
            $movie->runtime = $movieData['runtime'];
            $movie->services = $movieData['services'];
            $movie->search_term = $request->searchTerm;
            // $movie->featured = 0;
            // $movie->amc = 0;

            if ($list === 'upcoming') {
                $movie->released = false;
                $movie->release_date = $movieData['releaseDate'];
            }

            if ($list === 'history') {
                $movie->watched = true;
                $movie->watched_date = $currentDate;
            }

            if ($list === 'watch') {
                $movie->add_date = $currentDate;
                // TODO: add_date can be replaced with created_at
            }

            $movie->save();
        }

        return response()->json([
            'message' => 'Movie Added.',
            'movie' => $movie,
        ]);
    }

    public function updateMovie(Request $request): JsonResponse
    {
        return response()->json([]);
    }

    public function deleteMovie(int $id)
    {
        if (Movie::where('id', $id)->exists()) {
            $movie = Movie::find($id);
            $movie->delete();

            return response()->json([
                'message' => 'Movie deleted.'
            ]);
        } else {
            return response()->json([
                'message' => 'Movie not found.'
            ]);
        }
    }
}
