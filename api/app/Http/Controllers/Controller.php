<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Controller extends BaseController {
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public $loggedIn = false;

    public function __construct(Request $request) {

        $token = $request->cookie('auth_token');

        Log::info("token: " . $token);

        if (!$token) {
            return;
        }

        $response = Http::withToken($token)->get(env('MAIN_API_URL') . '/user');

        Log::info($response->json());
        Log::info($response->body());

        if ($response->json('user')) {
            $this->loggedIn = true;
            Log::info("success");
        } else {
            Log::info("failed");
        }
    }
}
