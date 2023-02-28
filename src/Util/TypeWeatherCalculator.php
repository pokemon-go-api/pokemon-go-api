<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Util;

use PokemonGoApi\PogoAPI\Types\PokemonType;
use PokemonGoApi\PogoAPI\Types\WeatherBoost;

use function array_values;
use function in_array;

final class TypeWeatherCalculator
{
    /** @return array<int, WeatherBoost> */
    public function getWeatherBoost(PokemonType $primaryType, PokemonType $secondaryType): array
    {
        $boostedWeather = [];
        foreach (WeatherBoost::ALL_WEATHER_TYPES as $weatherType) {
            $weather = WeatherBoost::createFromType($weatherType);
            if (in_array($primaryType->getType(), $weather->getBoostedTypes())) {
                $boostedWeather[$weather->getWeather()] = $weather;
            }

            if (! in_array($secondaryType->getType(), $weather->getBoostedTypes())) {
                continue;
            }

            $boostedWeather[$weather->getWeather()] = $weather;
        }

        return array_values($boostedWeather);
    }
}
