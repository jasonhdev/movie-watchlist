<?php

namespace App\Services;

use App\Models\AmcData;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use App\Services\DB;

class MovieService
{
    public function searchMovie(string $searchTerm): ?array
    {
        $process = new Process([env("PYTHON_PATH"), resource_path() . "/python/movieScraper.py", $searchTerm]);
        
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $movieData = json_decode($process->getOutput(), true);

        // Check if movie is playing at AMC
        $titleCount = 0;
        if ($movieData) {
            $titleCount = AmcData::select('*')
                ->where('title', 'LIKE', "%$searchTerm%")
                ->orWhere('title', 'LIKE', "%" . $movieData['title'] . "%")
                ->count();
        }

        $movieData['amc'] = $titleCount >= 1;

        return $movieData;
    }
}
