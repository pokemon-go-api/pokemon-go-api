<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Types;

use Exception;

use function method_exists;
use function strtolower;

final class WeatherBoost
{
    public const SUNNY         = 'sunny';
    public const RAIN          = 'rain';
    public const PARTLY_CLOUDY = 'partlyCloudy';
    public const CLOUDY        = 'cloudy';
    public const WINDY         = 'windy';
    public const SNOW          = 'snow';
    public const FOG           = 'fog';
    public const EXTREME       = 'extreme';

    public const ALL_WEATHER_TYPES = [
        self::SUNNY,
        self::RAIN,
        self::PARTLY_CLOUDY,
        self::CLOUDY,
        self::WINDY,
        self::SNOW,
        self::FOG,
        self::EXTREME,
    ];

    private string $weather;
    /** @var PokemonType[] */
    private array $boostedTypes;

    public function __construct(string $weather, PokemonType ...$boostedTypes)
    {
        $this->weather      = $weather;
        $this->boostedTypes = $boostedTypes;
    }

    public static function createFromType(string $weatherType): self
    {
        $typeNormalized = strtolower($weatherType);
        if (method_exists(self::class, $typeNormalized)) {
            return self::{$typeNormalized}();
        }

        throw new Exception('weather type ' . $weatherType . ' does not exists', 1609070869725);
    }

    public function getAssetsName(): string
    {
        if ($this->weather === self::PARTLY_CLOUDY) {
            return 'partlycloudy_day';
        }

        return $this->weather;
    }

    public function getWeather(): string
    {
        return $this->weather;
    }

    public function getWeatherTranslationKey(): string
    {
        switch ($this->weather) {
            case self::PARTLY_CLOUDY:
                return 'partly_cloudy';

            case self::CLOUDY:
                return 'overcast';

            case self::RAIN:
                return 'rainy';

            default:
                return $this->weather;
        }
    }

    /** @return array<int, PokemonType> */
    public function getBoostedTypes(): array
    {
        return $this->boostedTypes;
    }

    public static function sunny(): self
    {
        return new self(self::SUNNY, ...[
            PokemonType::ground(),
            PokemonType::fire(),
            PokemonType::grass(),
        ]);
    }

    public static function rain(): self
    {
        return new self(self::RAIN, ...[
            PokemonType::electric(),
            PokemonType::bug(),
            PokemonType::water(),
        ]);
    }

    public static function partlyCloudy(): self
    {
        return new self(self::PARTLY_CLOUDY, ...[
            PokemonType::rock(),
            PokemonType::normal(),
        ]);
    }

    public static function cloudy(): self
    {
        return new self(self::CLOUDY, ...[
            PokemonType::fairy(),
            PokemonType::poison(),
            PokemonType::fighting(),
        ]);
    }

    public static function windy(): self
    {
        return new self(self::WINDY, ...[
            PokemonType::dragon(),
            PokemonType::flying(),
            PokemonType::psychic(),
        ]);
    }

    public static function snow(): self
    {
        return new self(self::SNOW, ...[
            PokemonType::ice(),
            PokemonType::steel(),
        ]);
    }

    public static function fog(): self
    {
        return new self(self::FOG, ...[
            PokemonType::ghost(),
            PokemonType::dark(),
        ]);
    }

    public static function extreme(): self
    {
        return new self(self::EXTREME);
    }
}
