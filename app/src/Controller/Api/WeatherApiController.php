<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Weather\Application\Exception\CityNotFound;
use App\Weather\Application\GetWeatherByCity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class WeatherApiController extends AbstractController
{
    #[Route('/api/weather', name: 'api_weather_show', methods: ['GET'])]
    public function __invoke(Request $request, GetWeatherByCity $getWeatherByCity): JsonResponse
    {
        $city = trim((string) $request->query->get('city', ''));

        if ($city === '') {
            return $this->json([
                'error' => 'Query parameter "city" is required.',
            ], 400);
        }

        try {
            $report = $getWeatherByCity->handle($city);

            return $this->json($report->toArray());
        } catch (CityNotFound $exception) {
            return $this->json([
                'error' => $exception->getMessage(),
            ], 404);
        }
    }
}
