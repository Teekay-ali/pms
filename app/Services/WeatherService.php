<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeatherService
{
    public function getWeather(string $location): ?array
    {
        $key = config('services.openweathermap.key');

        if (!$key || !$location) return null;

        try {
            $response = Http::timeout(5)->get('https://api.openweathermap.org/data/2.5/weather', [
                'q'     => $location,
                'appid' => $key,
                'units' => 'metric',
            ]);

            if ($response->failed()) return null;

            $data = $response->json();

            return [
                'weather'     => ucfirst($data['weather'][0]['description'] ?? ''),
                'temperature' => round($data['main']['temp'] ?? 0, 1),
                'icon'        => $data['weather'][0]['icon'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::warning('Weather fetch failed: ' . $e->getMessage());
            return null;
        }
    }
}
