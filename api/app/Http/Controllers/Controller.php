<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public $loggedIn = false;

    public function __construct(Request $request)
    {
        $allowedTokens = explode(',', env('TOKENS'));

        $token = $request->bearerToken();
        if ($token && in_array($token, $allowedTokens)) {
            $this->loggedIn = true;
        }
    }

}
