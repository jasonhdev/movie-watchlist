<?php

namespace App\Services;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class MovieService
{
    public function searchMovie(string $searchTerm): ?array
    {
        $pythonPath = resource_path() . "/python/";
        $process = new Process([$pythonPath . ".env/Scripts/python.exe", $pythonPath . 'movieScraper.py', $searchTerm]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return json_decode($process->getOutput(), true);
    }
}
