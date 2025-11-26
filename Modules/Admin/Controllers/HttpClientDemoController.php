<?php

namespace Modules\Admin\Controllers;

use App\Core\Application;

/**
 * HTTP Client Demo Controller
 * 
 * Demonstrates various uses of the HTTP Client
 */
class HttpClientDemoController
{
    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Example 1: Simple GET request to GitHub API
     */
    public function githubUser()
    {
        try {
            // Get GitHub user info
            $response = Http()->get('https://api.github.com/users/torvalds');

            if ($response->successful()) {
                $user = $response->json();

                return $this->app->view->render('backend.http-demo.github', [
                    'user' => $user,
                    'success' => true,
                ]);
            }

            return $this->app->view->render('backend.http-demo.github', [
                'error' => 'Failed to fetch user',
                'success' => false,
            ]);
        } catch (\Exception $e) {
            return $this->app->view->render('backend.http-demo.github', [
                'error' => $e->getMessage(),
                'success' => false,
            ]);
        }
    }

    /**
     * Example 2: POST request with JSON data
     */
    public function postExample()
    {
        try {
            // Example: Post to JSONPlaceholder API (test API)
            $response = Http()->post('https://jsonplaceholder.typicode.com/posts', [
                'title' => 'Test Post from SunuFramework',
                'body' => 'This is a test post created using HTTP Client',
                'userId' => 1,
            ]);

            if ($response->successful()) {
                $post = $response->json();

                return json_encode([
                    'success' => true,
                    'message' => 'Post created successfully',
                    'data' => $post,
                ]);
            }
        } catch (\Exception $e) {
            return json_encode([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Example 3: Request with authentication
     */
    public function authenticatedRequest()
    {
        try {
            // Example: Get user repos from GitHub (requires token for private repos)
            $token = config('services.github.token'); // Add token to config

            $response = Http()->withToken($token)
                ->get('https://api.github.com/user/repos');

            if ($response->successful()) {
                $repos = $response->json();

                return json_encode([
                    'success' => true,
                    'count' => count($repos),
                    'repos' => array_slice($repos, 0, 5), // First 5
                ]);
            }
        } catch (\Exception $e) {
            return json_encode([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Example 4: Timeout and retry
     */
    public function withRetry()
    {
        try {
            // Request with timeout and retry
            $response = Http()
                ->timeout(10)          // 10 seconds timeout
                ->retry(3, 100)        // 3 retries, 100ms between
                ->get('https://httpbin.org/delay/2'); // Simulates 2s delay

            if ($response->successful()) {
                return json_encode([
                    'success' => true,
                    'message' => 'Request successful',
                    'data' => $response->json(),
                ]);
            }
        } catch (\Exception $e) {
            return json_encode([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Example 5: Multiple requests (weather API example)
     */
    public function weatherExample()
    {
        try {
            // Example with OpenWeatherMap API
            // Get city coordinates
            $cityResponse = Http()->get('https://api.openweathermap.org/geo/1.0/direct', [
                'q' => 'Dakar,SN',
                'limit' => 1,
                'appid' => config('services.openweather.key'),
            ]);

            if ($cityResponse->successful()) {
                $cityData = $cityResponse->json();

                if (!empty($cityData)) {
                    $lat = $cityData[0]['lat'];
                    $lon = $cityData[0]['lon'];

                    // Get weather for coordinates
                    $weatherResponse = Http()->get('https://api.openweathermap.org/data/2.5/weather', [
                        'lat' => $lat,
                        'lon' => $lon,
                        'units' => 'metric',
                        'appid' => config('services.openweather.key'),
                    ]);

                    if ($weatherResponse->successful()) {
                        $weather = $weatherResponse->json();

                        return json_encode([
                            'success' => true,
                            'city' => 'Dakar',
                            'temperature' => $weather['main']['temp'],
                            'description' => $weather['weather'][0]['description'],
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            return json_encode([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Example 6: Check API status
     */
    public function checkApiStatus()
    {
        $apis = [
            'GitHub' => 'https://api.github.com',
            'JSONPlaceholder' => 'https://jsonplaceholder.typicode.com',
            'HTTPBin' => 'https://httpbin.org/status/200',
        ];

        $results = [];

        foreach ($apis as $name => $url) {
            try {
                $response = Http()->timeout(5)->get($url);

                $results[$name] = [
                    'status' => $response->status(),
                    'success' => $response->successful(),
                    'message' => $response->successful() ? 'Online' : 'Error',
                ];
            } catch (\Exception $e) {
                $results[$name] = [
                    'status' => 0,
                    'success' => false,
                    'message' => 'Offline: ' . $e->getMessage(),
                ];
            }
        }

        return json_encode([
            'success' => true,
            'apis' => $results,
        ]);
    }
}
