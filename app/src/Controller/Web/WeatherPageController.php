<?php

declare(strict_types=1);

namespace App\Controller\Web;

use App\Weather\Application\Exception\CityNotFound;
use App\Weather\Application\GetWeatherByCity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class WeatherPageController extends AbstractController
{
    #[Route('/', name: 'weather_page', methods: ['GET', 'POST'])]
    public function __invoke(Request $request, GetWeatherByCity $getWeatherByCity): Response
    {
        $city = '';
        $report = null;
        $error = null;

        if ($request->isMethod('POST')) {
            $city = trim((string) $request->request->get('city', ''));

            if ($city === '') {
                $error = 'Please enter a city.';
            } else {
                try {
                    $report = $getWeatherByCity->handle($city);
                } catch (CityNotFound $exception) {
                    $error = $exception->getMessage();
                }
            }
        }

        return $this->render('weather/index.html.twig', [
            'city' => $city,
            'report' => $report,
            'error' => $error,
        ]);
    }
}
