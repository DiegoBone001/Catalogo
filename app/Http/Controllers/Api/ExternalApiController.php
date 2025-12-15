<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ExternalApiController extends Controller
{
    /**
     * Get weather data from OpenWeather API for Quito, Ecuador
     */
    public function getWeather()
    {
        // Usar cache de 10 minutos para evitar llamadas excesivas
        $weatherData = Cache::remember('weather_quito', 600, function () {
            // TEMPORAL: Datos simulados de clima de Quito
            // Para usar la API real, descomenta el código de abajo
            return [
                'success' => true,
                'city' => 'Quito',
                'temperature' => 11, // Temperatura típica de Quito
                'feels_like' => 10,
                'description' => 'Nubes dispersas',
                'icon' => '03d',
                'humidity' => 75,
                'wind_speed' => 3.5,
            ];
            
            /* CÓDIGO ORIGINAL (descomenta cuando tengas una API key válida):
            try {
                $response = Http::timeout(10)->get('https://api.openweathermap.org/data/2.5/weather', [
                    'q' => 'Quito,EC',
                    'units' => 'metric',
                    'lang' => 'es',
                    'appid' => env('OPENWEATHER_API_KEY', ''),
                ]);

                if ($response->successful()) {
                    $weatherData = $response->json();
                    
                    return [
                        'success' => true,
                        'city' => $weatherData['name'],
                        'temperature' => round($weatherData['main']['temp']),
                        'feels_like' => round($weatherData['main']['feels_like']),
                        'description' => ucfirst($weatherData['weather'][0]['description']),
                        'icon' => $weatherData['weather'][0]['icon'],
                        'humidity' => $weatherData['main']['humidity'],
                        'wind_speed' => $weatherData['wind']['speed'],
                    ];
                }

                return [
                    'success' => false,
                    'message' => 'Error al obtener datos del clima'
                ];

            } catch (\Exception $e) {
                return [
                    'success' => false,
                    'message' => 'Error de conexión con la API del clima',
                    'error' => $e->getMessage()
                ];
            }
            */
        });

        return response()->json($weatherData, $weatherData['success'] ? 200 : 500);
    }
}
