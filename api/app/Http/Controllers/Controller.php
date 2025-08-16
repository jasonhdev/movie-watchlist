<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class Controller extends BaseController {
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public $loggedIn = false;

    public function __construct(Request $request) {
        $token = $request->cookie('auth_token');

        $response = Http::withHeaders([
            'Cookie' => "auth_token={$token}" // send cookie to Auth service
        ])->get(env('MAIN_API_URL') . '/user');

        if ($response->successful() && $response->json('user')) {
            $this->loggedIn = true;
        }
    }
}
