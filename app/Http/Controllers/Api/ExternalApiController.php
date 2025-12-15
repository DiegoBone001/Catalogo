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
            try {
                // API de OpenWeather (sin necesidad de API key para clima actual)
                // Usando la versión gratuita sin autenticación
                $response = Http::timeout(10)->get('https://api.openweathermap.org/data/2.5/weather', [
                    'q' => 'Quito,EC',
                    'units' => 'metric',
                    'lang' => 'es',
                    'appid' => env('OPENWEATHER_API_KEY', ''), // Opcional: agregar API key en .env
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    
                    return [
                        'success' => true,
                        'city' => $data['name'],
                        'temperature' => round($data['main']['temp']),
                        'feels_like' => round($data['main']['feels_like']),
                        'description' => ucfirst($data['weather'][0]['description']),
                        'icon' => $data['weather'][0]['icon'],
                        'humidity' => $data['main']['humidity'],
                        'wind_speed' => $data['wind']['speed'],
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
        });

        return response()->json($weatherData, $weatherData['success'] ? 200 : 500);
    }
}
