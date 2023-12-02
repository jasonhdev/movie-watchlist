<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Movie;
use Illuminate\Http\JsonResponse;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class MovieController extends Controller
{
    const LIST_WATCH = "watch";
    const LIST_UPCOMING = "upcoming";
    const LIST_HISTORY = "history";
    const LIST_AMC = "amc";

    const ACTION_WATCH = "watch";
    const ACTION_FEATURE = "feature";
    const ACTION_DELETE = "delete";
    const ACTION_REFRESH = "refresh";

    public function index(Request $request): JsonResponse
    {
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
                    ->orderBy('add_date', 'desc')
                    ->get();
                break;
            case self::LIST_AMC:
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
        // return response()->json([
        //     'success' => true,
        //     'movieData' => "{\"title\": \"Interstellar\", \"description\": \"In Earth's future, a global crop blight and second Dust Bowl are slowly rendering the planet uninhabitable. Professor Brand (Michael Caine), a brilliant NASA physicist, is working on plans to save mankind by transporting Earth's population to a new home via a wormhole. But first, Brand must send former NASA pilot Cooper (Matthew McConaughey) and a team of researchers through the wormhole and across the galaxy to find out which of three planets could be mankind's new home.\\u2026\\u00a0MORE\", \"tomato\": \"73%\", \"imdb\": \"8.7/10\", \"image\": \"//upload.wikimedia.org/wikipedia/en/b/bc/Interstellar_film_poster.jpg\", \"trailer\": \"https://www.youtube.com/attribution_link?utm_campaign=ytcore&yt_product=ytalc&yt_goal=acq&utm_source=int&utm_medium=gs&utm_content=ump&yt_campaign_id=ytalc22&c=ytcore-ytalc-acq-int-gs-ump-ytalc22&utm_term=video&u=https%3A%2F%2Fwww.youtube.com%2Fwatch%3Fv%3DoCjW6gdEDa4\", \"rating\": \"PG-13\", \"year\": \"2014\", \"genre\": \"Sci-fi/Adventure\", \"runtime\": \"2h 49m\", \"services\": \"Mgmplus\", \"torrent\": \"https://yts.mx/movies/interstellar-2014\", \"releaseDate\": \"October 26, 2014\", \"watched\": 0}\r\n",
        // ]);

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
            // $movie->amc = 0;

            if ($list === self::LIST_UPCOMING) {
                $movie->released = false;
                $movie->release_date = $movieData['releaseDate'];
            }

            if ($list === self::LIST_HISTORY) {
                $movie->watched = true;
                $movie->watched_date = $currentDate;
            }

            if ($list === self::LIST_WATCH) {
                $movie->add_date = $currentDate;
                // TODO: add_date can be replaced with created_at
            }

            $movie->save();
        }

        return response()->json([
            'message' => 'Movie added.',
            'movie' => $movie,
        ]);
    }

    public function updateMovie(Request $request, $id): JsonResponse
    {
        $currentDate = date("Y-m-d H:i:s");

        if (Movie::where('id', $id)->exists()) {
            $movie = Movie::find($id);

            $action = $request->get('action');

            switch ($action) {
                case self::ACTION_WATCH:
                    $movie->watched = $request->watched;
                    $movie->watched_date = $currentDate;
                    break;

                case self::ACTION_FEATURE:
                    $movie->featured = $request->featured;
                    break;

                case self::ACTION_REFRESH:
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
                    }
                    break;

                default:
                    break;
            }

            $movie->save();

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
