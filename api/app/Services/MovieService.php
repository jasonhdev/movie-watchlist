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
        $pythonPath = resource_path() . "/python/";
        $process = new Process([$pythonPath . ".env/Scripts/python.exe", $pythonPath . 'movieScraper.py', $searchTerm]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $movieData = json_decode($process->getOutput(), true);

        // Check if movie is playing at AMC
        if ($movieData) {
            $titleCount = AmcData::select('*')
                ->where('title', 'LIKE', "%$searchTerm%")
                ->orWhere('title', 'LIKE', "%" . $movieData['title'] . "%")
                ->count();

            $movieData['amc'] = $titleCount >= 1;
        }

        return $movieData;
    }
}
