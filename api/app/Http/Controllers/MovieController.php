<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Movie;

class MovieController extends Controller
{
    public function index(Request $request)
    {
        $list = $request->input('list');

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
                // TODO: case "amc":
            default:
                $movies = Movie::all();
                break;
        }

        return response()->json($movies);
    }
}
