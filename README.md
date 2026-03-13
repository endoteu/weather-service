# Weather Service

Small Symfony-based weather service built as an interview task.

The service exposes:
- a simple web UI built with Twig
- an API endpoint for fetching weather by city

It returns:
- the current temperature for a city
- a trend suffix based on the deviation from the average temperature for the last 10 days

Example response:
- `4 🥵`
- `4 🥶`
- `4 -`

## Tech stack

- PHP 8.3
- Symfony 7.4
- Twig
- MariaDB
- Redis
- Docker Compose
- Behat

## Why Symfony 7.4 and not Symfony 8

The requirement was to use PHP 8.3 and the latest stable Symfony version possible.

Symfony 8 requires PHP 8.4+, so for PHP 8.3 the correct choice is Symfony 7.4 LTS. The choice of PHP 8.3 is a personal decision, and it is not specified in the requirements.

## Project structure

The code is organized in a DDD-style structure inside the `Weather` context:

- `src/Weather/Domain`  
  Core domain objects and contracts
- `src/Weather/Application`  
  Use case orchestration
- `src/Weather/Infrastructure`  
  Doctrine persistence, external API integration, caching-related infrastructure
- `src/Controller`  
  HTTP entry points
- `templates/`  
  Twig UI
- `features/`  
  Behat scenarios

## Main behavior

The client provides a city and receives:
- current temperature
- trend suffix

Trend rules:
- if current temperature is higher than the average of the last 10 days → `🥵`
- if current temperature is lower than the average of the last 10 days → `🥶`
- if equal → `-`

The trend calculation is intentionally simple:
`currentTemperature - averageLast10Days`

## Bonus requirement: minimizing repeated third-party calls

The application minimizes repeated requests for the same city within a 1-hour window by using:

- Redis cache for the final report
- MariaDB for persisted weather snapshots
- Symfony Lock with Redis to avoid parallel duplicate third-party calls for the same city

Typical flow:
1. Request comes for a city
2. Check Redis cache
3. If missing, acquire a lock for that city
4. Check if a fresh snapshot already exists in the database
5. If not, fetch from third-party provider
6. Persist snapshot in MariaDB
7. Cache final report in Redis

This prevents unnecessary repeated network calls when multiple clients request the same city close in time.

## Third-party weather source

The implementation uses Open-Meteo:
- geocoding API for city → coordinates
- forecast API for current temperature
- archive API for the last 10 days average

## Running locally

No local PHP, Composer, Redis or database installation is required.

UI: http://localhost:8080

API: http://localhost:8000/api/weather/Sofia

## Running Tests
```bash
docker compose exec php vendor/bin/behat
```
### Start containers

```bash
docker compose up -d --build
docker compose run --rm composer install
docker compose exec php php bin/console doctrine:database:create --if-not-exists
docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction
```