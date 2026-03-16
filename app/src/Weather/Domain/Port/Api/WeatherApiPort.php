<?php

declare(strict_types=1);

namespace App\Weather\Domain\Port\Api;

use App\Weather\Application\Contract\WeatherQueryInterface;
use App\Weather\Application\Exception\CityNotFound;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class WeatherApiPort extends AbstractController
{
    #[Route('/api/weather', name: 'api_weather_show', methods: ['GET'])]
    public function __invoke(Request $request, WeatherQueryInterface $weatherQuery): JsonResponse
    {
        $city = trim((string) $request->query->get('city', ''));

        if ($city === '') {
            return $this->json([
                'error' => 'Query parameter "city" is required.',
            ], 400);
        }

        try {
            $report = $weatherQuery->getByCity($city);

            return $this->json($report->toArray());
        } catch (CityNotFound $exception) {
            return $this->json([
                'error' => $exception->getMessage(),
            ], 404);
        }
    }
}
