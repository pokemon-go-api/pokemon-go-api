<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Types;

use Exception;

use function array_values;
use function assert;
use function method_exists;
use function strtolower;

final class WeatherBoost
{
    public const string SUNNY = 'sunny';

    public const string RAIN = 'rain';

    public const string PARTLY_CLOUDY = 'partlyCloudy';

    public const string CLOUDY = 'cloudy';

    public const string WINDY = 'windy';

    public const string SNOW = 'snow';

    public const string FOG = 'fog';

    public const string EXTREME = 'extreme';

    public const array ALL_WEATHER_TYPES = [
        self::SUNNY,
        self::RAIN,
        self::PARTLY_CLOUDY,
        self::CLOUDY,
        self::WINDY,
        self::SNOW,
        self::FOG,
        self::EXTREME,
    ];

    /** @var list<PokemonType> */
    private readonly array $boostedTypes;

    public function __construct(private readonly string $weather, PokemonType ...$boostedTypes)
    {
        $this->boostedTypes = array_values($boostedTypes);
    }

    public static function createFromType(string $weatherType): self
    {
        $typeNormalized = strtolower($weatherType);
        if (method_exists(self::class, $typeNormalized)) {
            $self = self::{$typeNormalized}();
            assert($self instanceof self);

            return $self;
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
        return match ($this->weather) {
            self::PARTLY_CLOUDY => 'partly_cloudy',
            self::CLOUDY => 'overcast',
            self::RAIN => 'rainy',
            default => $this->weather,
        };
    }

    /** @return list<PokemonType> */
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
