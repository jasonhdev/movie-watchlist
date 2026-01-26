<?php

namespace App\Services;

use App\Models\AmcData;
use App\Models\Movie;
use Exception;
use Illuminate\Support\Facades\Log;
use Aws\Sqs\SqsClient;
use Aws\Credentials\Credentials;
use Illuminate\Support\Str;

class MovieService {

    public function searchMovie(string $searchTerm): ?array {
        try {
            $credentials = new Credentials(env('AWS_ACCESS_ID'), env('AWS_ACCESS_KEY'));
            $sqs = new SqsClient([
                'region' => 'us-west-1',
                'credentials' => $credentials,
            ]);

            Log::info("Sending job");
            $jobId = (string) Str::uuid();
            $sqs->sendMessage([
                'QueueUrl' => env('SQS_QUEUE_URL'),
                'MessageBody' => json_encode([
                    'job_id' => $jobId,
                    'action' => 'scrape_movie',
                    'payload' => ['search' => $searchTerm],
                ]),
            ]);

            sleep(3);
            $start = time();
            $timeout = 30;
            
            $movieData = [];
            while (time() - $start < $timeout) {
                $result = $sqs->receiveMessage([
                    'QueueUrl' => env('SQS_RESULTS_QUEUE_URL'),
                    'MaxNumberOfMessages' => 10,
                    'WaitTimeSeconds' => 5
                ]);

                if (empty($result['Messages'])) {
                    Log::info("No messages, continuing");
                    continue;
                }

                foreach ($result['Messages'] as $msg) {
                    $body = json_decode($msg['Body'], true);
                    $receipt = $msg['ReceiptHandle'];

                    if (($body['job_id'] ?? null) === $jobId) {
                        Log::info("Job results found");

                        $sqs->deleteMessage([
                            'QueueUrl' => env('SQS_RESULTS_QUEUE_URL'),
                            'ReceiptHandle' => $receipt
                        ]);

                        $movieData = $body['payload'] ?? [];
                        break 2;
                    }
                }
            }
        } catch (Exception $e) {
            Log::error("Error in scraper: " . $e->getMessage());
            return ['title' => $searchTerm];
        }

        if (!$movieData || !isset($movieData['title'])) {
            $movieData['title'] = $searchTerm;
        }

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

    public function getRefreshedMovieData(Movie $movie): Movie {
        if ($movieData = $this->searchMovie($movie->search_term ?? $movie->title)) {
            $movie->title = $movieData['title'] ?? $movie->title;
            $movie->description = $movieData['description'] ?? $movie->description;
            $movie->tomato = $movieData['tomato'] ?? $movie->tomato;
            $movie->imdb = $movieData['imdb'] ?? $movie->imdb;
            $movie->poster_url = $movieData['image'] ??  $movie->poster_url;
            $movie->trailer_url = $movieData['trailer'] ?? $movie->trailer_url;
            $movie->rating = $movieData['rating'] ?? $movie->rating;
            $movie->year = $movieData['year'] ?? $movie->year;
            $movie->genre = $movieData['genre'] ?? $movie->genre;
            $movie->runtime = $movieData['runtime'] ?? $movie->runtime;
            $movie->services = $movieData['services'] ?? $movie->services;
            $movie->release_date = $movieData['releaseDate'] ?? $movie->release_date;
            $movie->amc = $movieData['amc'] ?? 0;
        }

        $releaseDate = $movie->release_date;
        if (null !== $releaseDate && !$movie->released) {
            $movie->released = strtotime($releaseDate) < strtotime("today");
        }

        return $movie;
    }
}
